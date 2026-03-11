<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Asignacion.php';
require_once __DIR__ . '/../models/DetalleAsignacion.php';
require_once __DIR__ . '/../models/Competencia.php';

class AsignacionController extends Controller
{
    protected $controllerName = 'asignacion';

    function __construct()
    {
    }

    /**
     * Establece el usuario que realizará la operación para auditoría
     * Debe llamarse ANTES de cualquier INSERT, UPDATE o DELETE en ASIGNACION
     */
    private function setUsuarioAuditoria()
    {
        try {
            require_once __DIR__ . '/../Database.php';
            $db = Database::getInstance()->getConnection();
            
            // Obtener correo del usuario logueado desde la sesión
            // La sesión guarda el correo en 'user_correo' (ver AuthController)
            $correoUsuario = $_SESSION['user_correo'] ?? 'sistema@sena.edu.co';
            
            // Establecer variable de sesión MySQL para los triggers
            $stmt = $db->prepare("SET @usuario_correo = ?");
            $stmt->execute([$correoUsuario]);
            
            // Log para debugging (opcional, puedes comentar después)
            error_log("Auditoría: Usuario establecido como: " . $correoUsuario);
        } catch (Exception $e) {
            // Si falla, continuar con el valor por defecto del trigger
            error_log("Error al establecer usuario de auditoría: " . $e->getMessage());
        }
    }

    function index()
    {
        require_once('views/asignacion/index.php');
    }

    function register()
    {
        require_once __DIR__ . '/../models/Instructor.php';
        require_once __DIR__ . '/../models/Ficha.php';
        require_once __DIR__ . '/../models/Ambiente.php';
        require_once __DIR__ . '/../models/Competencia.php';

        // Si es coordinador, filtrar instructores y fichas por centro de formación
        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);
        if ($coor_id) {
            require_once __DIR__ . '/../models/Coordinacion.php';
            require_once __DIR__ . '/../Database.php';
            $coordinacion = Coordinacion::find($coor_id);
            if ($coordinacion) {
                $instructores = Instructor::getByCentroFormacion($coordinacion->getCentroFormacionId());

                // Filtrar fichas por coordinación
                $db = Database::getInstance();
                $sql = "SELECT fich_id, PROGRAMA_prog_id, INSTRUCTOR_inst_id_lider, fich_jornada, 
                               COORDINACION_coord_id, fich_fecha_ini_lectiva, fich_fecha_fin_lectiva 
                        FROM FICHA 
                        WHERE COORDINACION_coord_id = ?
                        ORDER BY fich_id";
                $result = $db->select($sql, [$coor_id]);

                $fichas = [];
                foreach ($result as $row) {
                    $fichas[] = new Ficha(
                        $row['fich_id'],
                        $row['PROGRAMA_prog_id'],
                        $row['INSTRUCTOR_inst_id_lider'],
                        $row['fich_jornada'],
                        $row['COORDINACION_coord_id'],
                        $row['fich_fecha_ini_lectiva'],
                        $row['fich_fecha_fin_lectiva']
                    );
                }
            } else {
                $instructores = Instructor::all();
                $fichas = Ficha::all();
            }
        } else {
            $instructores = Instructor::all();
            $fichas = Ficha::all();
        }

        $ambientes = Ambiente::all();
        // No cargamos competencias aquí, se cargarán dinámicamente al seleccionar la ficha

        // Crear array de jornadas para JavaScript
        $jornadasFichas = [];
        foreach ($fichas as $ficha) {
            $jornadasFichas[$ficha->getId()] = $ficha->getJornada();
        }

        require_once('views/asignacion/create.php');
    }

    function save()
    {
        try {
            // Validar datos básicos
            $this->validarDatosBasicos($_POST);

            // Validar y procesar detalles de horarios
            $detalles = $this->procesarDetallesHorarios($_POST);

            // Validar coherencia entre fechas y horarios
            $this->validarCoherenciaFechasHorarios($_POST, $detalles);

            // NUEVA VALIDACIÓN: Verificar conflictos de horarios del instructor
            $this->validarConflictosInstructor($_POST['inst_id'], $detalles);

            // Calcular fecha fin basada en la última fecha del calendario
            $fechaFin = $this->calcularFechaFin($detalles);

            // AUDITORÍA: Establecer usuario antes de crear la asignación
            $this->setUsuarioAuditoria();

            // Crear asignación con fecha fin calculada
            $asignacion = new Asignacion(
                null,
                $_POST['inst_id'],
                $_POST['fich_id'],
                $_POST['amb_id'],
                $_POST['comp_id'],
                $_POST['asig_fecha_ini'],
                $fechaFin
            );

            $asignacionId = Asignacion::save($asignacion);

            // Guardar detalles de horarios
            foreach ($detalles as $detalle) {
                $detalle->setAsignacionId($asignacionId);
                DetalleAsignacion::save($detalle);
            }

            $mensaje = 'Asignación creada exitosamente. Fecha fin calculada automáticamente: ' . date('d/m/Y', strtotime($fechaFin));

            // Agregar advertencias de jornada si existen
            if (isset($_SESSION['advertencias_jornada'])) {
                $mensaje .= '<br><br><strong>Advertencias de jornada:</strong><br>';
                foreach ($_SESSION['advertencias_jornada'] as $advertencia) {
                    $mensaje .= '• ' . $advertencia . '<br>';
                }
                unset($_SESSION['advertencias_jornada']);
            }

            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
            $_SESSION['datos_formulario'] = $_POST;
        }

        $this->show();
    }

    private function validarDatosBasicos($datos)
    {
        if (
            empty($datos['inst_id']) || empty($datos['fich_id']) ||
            empty($datos['amb_id']) || empty($datos['comp_id']) ||
            empty($datos['asig_fecha_ini'])
        ) {
            throw new Exception('Todos los campos básicos son obligatorios');
        }

        // Ya no validamos fecha fin porque se calcula automáticamente
    }

    private function calcularFechaFin($detalles)
    {
        if (empty($detalles)) {
            throw new Exception('No se pueden calcular fechas sin horarios programados');
        }

        $fechas = [];
        foreach ($detalles as $detalle) {
            $fechas[] = $detalle->getFecha();
        }

        // Retornar la fecha más tardía
        return max($fechas);
    }

    private function procesarDetallesHorarios($datos)
    {
        $detalles = [];

        if (empty($datos['horarios'])) {
            throw new Exception('Debe programar al menos una clase en el calendario');
        }

        // Decodificar JSON de horarios
        $horarios = json_decode($datos['horarios'], true);
        if (!$horarios || !is_array($horarios)) {
            throw new Exception('Error en el formato de los horarios');
        }

        // Obtener información de la ficha para validar jornada
        require_once __DIR__ . '/../models/Ficha.php';
        $ficha = Ficha::find($datos['fich_id']);
        $jornadaFicha = $ficha ? $ficha->getJornada() : null;

        $advertenciasJornada = [];

        foreach ($horarios as $horario) {
            $fecha = $horario['fecha'] ?? '';
            $horaInicio = $horario['hora_inicio'] ?? '';
            $horaFin = $horario['hora_fin'] ?? '';

            if (empty($fecha) || empty($horaInicio) || empty($horaFin)) {
                throw new Exception('Todos los horarios deben tener fecha, hora de inicio y hora de fin');
            }

            // Validar que no sea domingo
            $diaSemana = date('w', strtotime($fecha)); // 0 = domingo
            if ($diaSemana == 0) {
                throw new Exception('No se pueden programar clases los domingos');
            }

            // Validar que la fecha sea posterior o igual a la fecha de inicio
            if ($fecha < $datos['asig_fecha_ini']) {
                throw new Exception("La fecha $fecha no puede ser anterior a la fecha de inicio de la asignación");
            }

            // Validar formato de horas (solo horas exactas)
            if (!preg_match('/^\d{2}:00$/', $horaInicio) || !preg_match('/^\d{2}:00$/', $horaFin)) {
                throw new Exception('Las horas deben ser exactas (ejemplo: 08:00, 14:00)');
            }

            if ($horaFin <= $horaInicio) {
                $fechaFormateada = date('d/m/Y', strtotime($fecha));
                throw new Exception("La hora de fin debe ser posterior a la hora de inicio para el día $fechaFormateada");
            }

            // Validar jornada (advertencia, no error)
            if ($jornadaFicha && !$this->validarHorarioJornada($horaInicio, $horaFin, $jornadaFicha)) {
                $fechaFormateada = date('d/m/Y', strtotime($fecha));
                $advertenciasJornada[] = "El horario del día $fechaFormateada ($horaInicio-$horaFin) no corresponde a la jornada {$jornadaFicha} de la ficha";
            }

            $detalles[] = new DetalleAsignacion(null, null, $fecha, $horaInicio, $horaFin);
        }

        // Si hay advertencias de jornada, las guardamos en sesión para mostrarlas
        if (!empty($advertenciasJornada)) {
            $_SESSION['advertencias_jornada'] = $advertenciasJornada;
        }

        return $detalles;
    }

    private function validarHorarioJornada($horaInicio, $horaFin, $jornada)
    {
        $inicio = (int) substr($horaInicio, 0, 2);
        $fin = (int) substr($horaFin, 0, 2);

        switch ($jornada) {
            case 'Mañana':
                // Jornada mañana: 6:00 - 12:00
                return $inicio >= 6 && $fin <= 12;
            case 'Tarde':
                // Jornada tarde: 12:00 - 18:00
                return $inicio >= 12 && $fin <= 18;
            case 'Noche':
                // Jornada noche: 18:00 - 22:00
                return $inicio >= 18 && $fin <= 22;
            case 'Mixta':
                // Jornada mixta: cualquier horario
                return true;
            default:
                return true;
        }
    }

    private function validarCoherenciaFechasHorarios($datos, $detalles)
    {
        // Obtener horas totales de la competencia
        $competencia = Competencia::find($datos['comp_id']);
        if (!$competencia) {
            throw new Exception('Competencia no encontrada');
        }

        $horasCompetencia = $competencia->getHoras();

        // Calcular horas totales programadas
        $horasTotalesProgramadas = DetalleAsignacion::calcularHorasTotales($detalles);

        if ($horasTotalesProgramadas <= 0) {
            throw new Exception('Los horarios definidos no son válidos');
        }

        // Validar si las horas programadas son suficientes (mínimo 80%)
        $minimoRequerido = ceil($horasCompetencia * 0.8);
        if ($horasTotalesProgramadas < $minimoRequerido) {
            $horasFaltantes = $minimoRequerido - $horasTotalesProgramadas;
            throw new Exception(
                "Debe programar al menos el 80% de las horas de la competencia. " .
                "Mínimo requerido: {$minimoRequerido} horas. " .
                "Horas programadas: {$horasTotalesProgramadas} horas. " .
                "Faltan {$horasFaltantes} horas para alcanzar el mínimo."
            );
        }

        // Validar que no haya conflictos de horarios en el mismo día
        $fechasUsadas = [];
        foreach ($detalles as $detalle) {
            $fecha = $detalle->getFecha();
            if (!isset($fechasUsadas[$fecha])) {
                $fechasUsadas[$fecha] = [];
            }

            $horaInicio = $detalle->getHoraInicio();
            $horaFin = $detalle->getHoraFin();

            // Verificar conflictos con otros horarios del mismo día
            foreach ($fechasUsadas[$fecha] as $horarioExistente) {
                if (($horaInicio < $horarioExistente['fin'] && $horaFin > $horarioExistente['inicio'])) {
                    $fechaFormateada = date('d/m/Y', strtotime($fecha));
                    throw new Exception("Hay conflicto de horarios en el día $fechaFormateada");
                }
            }

            $fechasUsadas[$fecha][] = ['inicio' => $horaInicio, 'fin' => $horaFin];
        }
    }

    /**
     * Validar conflictos de horarios del instructor
     */
    private function validarConflictosInstructor($instructorId, $detalles, $asignacionIdExcluir = null)
    {
        $conflictos = [];

        foreach ($detalles as $detalle) {
            $resultado = $this->verificarConflictoInstructor(
                $instructorId,
                $detalle->getFecha(),
                $detalle->getHoraInicio(),
                $detalle->getHoraFin(),
                $asignacionIdExcluir
            );

            if (!$resultado['disponible']) {
                foreach ($resultado['conflictos'] as $conflicto) {
                    $conflictos[] = $conflicto['mensaje'];
                }
            }
        }

        if (!empty($conflictos)) {
            $mensaje = "El instructor tiene conflictos de horarios:\n\n";
            foreach ($conflictos as $conflicto) {
                $mensaje .= "• " . $conflicto . "\n";
            }
            $mensaje .= "\nPor favor, modifique los horarios para evitar estos conflictos.";
            
            throw new Exception($mensaje);
        }
    }

    function show()
    {
        // Si es coordinador, filtrar asignaciones por fichas de su coordinación
        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);
        if ($coor_id) {
            require_once __DIR__ . '/../Database.php';
            $db = Database::getInstance();
            $sql = "SELECT a.ASIG_ID, a.INSTRUCTOR_inst_id, a.FICHA_fich_id, a.AMBIENTE_amb_id, 
                           a.COMPETENCIA_comp_id, a.asig_fecha_ini, a.asig_fecha_fin 
                    FROM ASIGNACION a
                    INNER JOIN FICHA f ON a.FICHA_fich_id = f.fich_id
                    WHERE f.COORDINACION_coord_id = ?
                    ORDER BY a.ASIG_ID";
            $result = $db->select($sql, [$coor_id]);

            $listaAsignaciones = [];
            foreach ($result as $row) {
                $listaAsignaciones[] = new Asignacion(
                    $row['ASIG_ID'],
                    $row['INSTRUCTOR_inst_id'],
                    $row['FICHA_fich_id'],
                    $row['AMBIENTE_amb_id'],
                    $row['COMPETENCIA_comp_id'],
                    $row['asig_fecha_ini'],
                    $row['asig_fecha_fin']
                );
            }
        } else {
            $listaAsignaciones = Asignacion::all();
        }
        require_once('views/asignacion/index.php');
    }

    function updateshow()
    {
        $id = $_GET['id'];
        $asignacion = Asignacion::find($id);
        $detallesAsignacion = DetalleAsignacion::searchByAsignacion($id);

        require_once __DIR__ . '/../models/Instructor.php';
        require_once __DIR__ . '/../models/Ficha.php';
        require_once __DIR__ . '/../models/Ambiente.php';
        require_once __DIR__ . '/../models/Competencia.php';

        // Si es coordinador, filtrar instructores y fichas por centro de formación
        $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);
        if ($coor_id) {
            require_once __DIR__ . '/../models/Coordinacion.php';
            require_once __DIR__ . '/../Database.php';
            $coordinacion = Coordinacion::find($coor_id);
            if ($coordinacion) {
                $instructores = Instructor::getByCentroFormacion($coordinacion->getCentroFormacionId());

                // Filtrar fichas por coordinación
                $db = Database::getInstance();
                $sql = "SELECT fich_id, PROGRAMA_prog_id, INSTRUCTOR_inst_id_lider, fich_jornada, 
                               COORDINACION_coord_id, fich_fecha_ini_lectiva, fich_fecha_fin_lectiva 
                        FROM FICHA 
                        WHERE COORDINACION_coord_id = ?
                        ORDER BY fich_id";
                $result = $db->select($sql, [$coor_id]);

                $fichas = [];
                foreach ($result as $row) {
                    $fichas[] = new Ficha(
                        $row['fich_id'],
                        $row['PROGRAMA_prog_id'],
                        $row['INSTRUCTOR_inst_id_lider'],
                        $row['fich_jornada'],
                        $row['COORDINACION_coord_id'],
                        $row['fich_fecha_ini_lectiva'],
                        $row['fich_fecha_fin_lectiva']
                    );
                }
            } else {
                $instructores = Instructor::all();
                $fichas = Ficha::all();
            }
        } else {
            $instructores = Instructor::all();
            $fichas = Ficha::all();
        }

        $ambientes = Ambiente::all();

        // Obtener competencias disponibles para la ficha + la competencia actual
        $competenciasDisponibles = Competencia::getCompetenciasDisponiblesPorFicha($asignacion->getFichaId());
        $competenciaActual = Competencia::find($asignacion->getCompetenciaId());

        // Agregar la competencia actual si no está en la lista de disponibles
        $competencias = $competenciasDisponibles;
        $yaIncluida = false;
        foreach ($competencias as $comp) {
            if ($comp->getId() == $competenciaActual->getId()) {
                $yaIncluida = true;
                break;
            }
        }
        if (!$yaIncluida && $competenciaActual) {
            array_unshift($competencias, $competenciaActual);
        }

        require_once('views/asignacion/edit.php');
    }

    function update()
    {
        try {
            // Validar datos básicos
            $this->validarDatosBasicos($_POST);

            // Validar y procesar detalles de horarios
            $detalles = $this->procesarDetallesHorarios($_POST);

            // Validar coherencia entre fechas y horarios
            $this->validarCoherenciaFechasHorarios($_POST, $detalles);

            // NUEVA VALIDACIÓN: Verificar conflictos de horarios del instructor (excluyendo la asignación actual)
            $this->validarConflictosInstructor($_POST['inst_id'], $detalles, $_POST['asig_id']);

            // Calcular fecha fin basada en la última fecha del calendario
            $fechaFin = $this->calcularFechaFin($detalles);

            // AUDITORÍA: Establecer usuario antes de actualizar la asignación
            $this->setUsuarioAuditoria();

            // Actualizar asignación con fecha fin calculada
            $asignacion = new Asignacion(
                $_POST['asig_id'],
                $_POST['inst_id'],
                $_POST['fich_id'],
                $_POST['amb_id'],
                $_POST['comp_id'],
                $_POST['asig_fecha_ini'],
                $fechaFin
            );

            Asignacion::update($asignacion);

            // Eliminar detalles anteriores y crear nuevos
            DetalleAsignacion::deleteByAsignacion($_POST['asig_id']);

            foreach ($detalles as $detalle) {
                $detalle->setAsignacionId($_POST['asig_id']);
                DetalleAsignacion::save($detalle);
            }

            $mensaje = 'Asignación actualizada exitosamente. Fecha fin calculada automáticamente: ' . date('d/m/Y', strtotime($fechaFin));

            // Agregar advertencias de jornada si existen
            if (isset($_SESSION['advertencias_jornada'])) {
                $mensaje .= '<br><br><strong>Advertencias de jornada:</strong><br>';
                foreach ($_SESSION['advertencias_jornada'] as $advertencia) {
                    $mensaje .= '• ' . $advertencia . '<br>';
                }
                unset($_SESSION['advertencias_jornada']);
            }

            $_SESSION['mensaje'] = $mensaje;
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
            $_SESSION['datos_formulario'] = $_POST;
        }

        $this->show();
    }

    function delete()
    {
        try {
            $id = $_GET['id'];
            
            // AUDITORÍA: Establecer usuario antes de eliminar la asignación
            $this->setUsuarioAuditoria();
            
            // Los detalles se eliminan automáticamente por CASCADE
            Asignacion::delete($id);
            $_SESSION['mensaje'] = 'Asignación eliminada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
        } catch (Exception $e) {
            $_SESSION['mensaje'] = 'Error al eliminar la asignación: ' . $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }

        $roleParam = isset($_GET['role']) ? '&role=' . $_GET['role'] : '';
        $coorParam = isset($_GET['coor_id']) ? '&coor_id=' . $_GET['coor_id'] : '';
        header('Location: index.php?c=asignacion&a=show' . $roleParam . $coorParam);
        exit;
    }

    function search()
    {
        if (!empty($_POST['asig_id'])) {
            $id = $_POST['asig_id'];
            $asignacion = Asignacion::find($id);
            $listaAsignaciones[] = $asignacion;
            require_once('views/asignacion/index.php');
        } else {
            $listaAsignaciones = Asignacion::all();
            require_once('views/asignacion/index.php');
        }
    }

    function detalles()
    {
        $id = $_GET['id'];
        $asignacion = Asignacion::find($id);
        
        if (!$asignacion) {
            $_SESSION['mensaje'] = 'Asignación no encontrada';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?controller=asignacion&action=show');
            exit;
        }
        
        $detallesAsignacion = DetalleAsignacion::searchByAsignacion($id);

        require_once __DIR__ . '/../models/Instructor.php';
        require_once __DIR__ . '/../models/Ficha.php';
        require_once __DIR__ . '/../models/Ambiente.php';
        require_once __DIR__ . '/../models/Competencia.php';

        $instructor = Instructor::find($asignacion->getInstructorId());
        $ficha = Ficha::find($asignacion->getFichaId());
        $ambiente = Ambiente::find($asignacion->getAmbienteId());
        $competencia = Competencia::find($asignacion->getCompetenciaId());

        require_once('views/detalleAsignacion/index.php');
    }

    /*
     * MÉTODO DESHABILITADO - Solo para uso interno/desarrollo
     * 
     * Este método permite ver el historial de auditoría de una asignación.
     * Se mantiene comentado para uso futuro en caso de necesitar auditorías.
     * 
     * Para habilitarlo, descomenta este método y agrega el botón en index.php
     */
    /*
    function historialAuditoria()
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $_SESSION['mensaje'] = 'ID de asignación no proporcionado';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?c=asignacion&a=show');
            exit;
        }

        // Obtener información de la asignación
        $asignacion = Asignacion::find($id);
        
        if (!$asignacion) {
            $_SESSION['mensaje'] = 'Asignación no encontrada';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?c=asignacion&a=show');
            exit;
        }

        // Obtener historial de auditoría
        require_once __DIR__ . '/../Database.php';
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT 
                    audit_id,
                    asig_id,
                    datos_anteriores,
                    datos_nuevos,
                    audit_fecha_hora,
                    audit_usuario_correo,
                    audit_accion
                FROM AUDITORIA_ASIGNACION
                WHERE asig_id = ?
                ORDER BY audit_fecha_hora DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decodificar JSON
        foreach ($historial as &$registro) {
            if ($registro['datos_anteriores']) {
                $registro['datos_anteriores'] = json_decode($registro['datos_anteriores'], true);
            }
            if ($registro['datos_nuevos']) {
                $registro['datos_nuevos'] = json_decode($registro['datos_nuevos'], true);
            }
        }

        // Cargar modelos necesarios para mostrar información
        require_once __DIR__ . '/../models/Instructor.php';
        require_once __DIR__ . '/../models/Ficha.php';
        require_once __DIR__ . '/../models/Ambiente.php';
        require_once __DIR__ . '/../models/Competencia.php';

        require_once('views/asignacion/historial.php');
    }
    */

    function error()
    {
        require_once('views/error.php');
    }

    /**
     * Mostrar historial de auditoría de una asignación
     */
    function historialAuditoria()
    {
        try {
            $this->validateGet(['id']);
            $asignacionId = $this->getGet('id');
            
            // Obtener la asignación
            require_once __DIR__ . '/../models/Asignacion.php';
            $asignacion = Asignacion::find($asignacionId);
            if (!$asignacion) {
                $this->redirectWithMessage('Asignación no encontrada', 'error');
                return;
            }
            
            // Obtener historial de auditoría
            require_once __DIR__ . '/../Database.php';
            $db = Database::getInstance();
            
            $sql = "SELECT audit_id, audit_fecha_hora, audit_accion, audit_usuario_correo, 
                           datos_anteriores, datos_nuevos 
                    FROM AUDITORIA_ASIGNACION 
                    WHERE asig_id = ? 
                    ORDER BY audit_fecha_hora DESC";
            
            $historialRaw = $db->select($sql, [$asignacionId]);
            
            // Procesar datos JSON
            $historial = [];
            foreach ($historialRaw as $registro) {
                $registro['datos_anteriores'] = $registro['datos_anteriores'] ? json_decode($registro['datos_anteriores'], true) : null;
                $registro['datos_nuevos'] = $registro['datos_nuevos'] ? json_decode($registro['datos_nuevos'], true) : null;
                $historial[] = $registro;
            }
            
            // Cargar modelos necesarios para la vista
            require_once __DIR__ . '/../models/Instructor.php';
            require_once __DIR__ . '/../models/Ambiente.php';
            require_once __DIR__ . '/../models/Competencia.php';
            
            $this->loadView('views/asignacion/historial.php', [
                'asignacion' => $asignacion,
                'historial' => $historial
            ]);
            
        } catch (Exception $e) {
            $this->redirectWithMessage('Error al cargar el historial: ' . $e->getMessage(), 'error');
        }
    }

    function getAsignacionesFicha()
    {
        header('Content-Type: application/json');

        try {
            $fichaId = $_GET['fich_id'] ?? null;

            if (!$fichaId) {
                echo json_encode(['success' => false, 'message' => 'ID de ficha no proporcionado']);
                return;
            }

            // Obtener todas las asignaciones de la ficha
            $asignaciones = Asignacion::searchByFicha($fichaId);
            $resultado = [];

            foreach ($asignaciones as $asignacion) {
                // Obtener detalles de cada asignación
                $detalles = DetalleAsignacion::searchByAsignacion($asignacion->getId());

                // Obtener información de la competencia
                $competencia = Competencia::find($asignacion->getCompetenciaId());
                $nombreCompetencia = $competencia ? $competencia->getNombreCorto() : 'Competencia';

                foreach ($detalles as $detalle) {
                    $resultado[] = [
                        'fecha' => $detalle->getFecha(),
                        'hora_inicio' => substr($detalle->getHoraInicio(), 0, 5), // HH:MM
                        'hora_fin' => substr($detalle->getHoraFin(), 0, 5), // HH:MM
                        'competencia' => $nombreCompetencia,
                        'asignacion_id' => $asignacion->getId()
                    ];
                }
            }

            echo json_encode(['success' => true, 'asignaciones' => $resultado]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    function getCompetenciasDisponibles()
    {
        header('Content-Type: application/json');

        try {
            $fichaId = $_GET['fich_id'] ?? null;

            if (!$fichaId) {
                echo json_encode(['success' => false, 'message' => 'ID de ficha no proporcionado']);
                return;
            }

            // Obtener competencias disponibles (del programa y no asignadas)
            $competencias = Competencia::getCompetenciasDisponiblesPorFicha($fichaId);

            $resultado = [];
            foreach ($competencias as $competencia) {
                $resultado[] = [
                    'id' => $competencia->getId(),
                    'nombre_corto' => $competencia->getNombreCorto(),
                    'nombre_unidad' => $competencia->getNombreUnidad(),
                    'horas' => $competencia->getHoras()
                ];
            }

            echo json_encode(['success' => true, 'competencias' => $resultado]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    function getInstructoresPorCompetencia()
    {
        header('Content-Type: application/json');

        try {
            $competenciaId = $_GET['comp_id'] ?? null;

            if (!$competenciaId) {
                echo json_encode(['success' => false, 'message' => 'ID de competencia no proporcionado']);
                return;
            }

            require_once __DIR__ . '/../models/Instructor.php';

            // Si es coordinador, filtrar por centro de formación
            $centroFormacionId = null;
            $coor_id = currentRole() === 'coordinador' ? currentUserId() : (isset($_GET['coor_id']) ? $_GET['coor_id'] : null);
            if ($coor_id) {
                require_once __DIR__ . '/../models/Coordinacion.php';
                $coordinacion = Coordinacion::find($coor_id);
                if ($coordinacion) {
                    $centroFormacionId = $coordinacion->getCentroFormacionId();
                }
            }

            // Obtener instructores que pueden dictar esta competencia (filtrados por centro si aplica)
            $instructores = Instructor::getInstructoresPorCompetencia($competenciaId, $centroFormacionId);

            $resultado = [];
            foreach ($instructores as $instructor) {
                $resultado[] = [
                    'id' => $instructor->getId(),
                    'nombre_completo' => $instructor->getNombres() . ' ' . $instructor->getApellidos()
                ];
            }

            echo json_encode(['success' => true, 'instructores' => $resultado]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    function verificarDisponibilidadAmbiente()
    {
        header('Content-Type: application/json');

        try {
            $ambienteId = $_POST['amb_id'] ?? null;
            $horarios = json_decode($_POST['horarios'] ?? '[]', true);
            $asignacionId = $_POST['asig_id'] ?? null; // Para edición

            if (!$ambienteId || !$horarios) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            require_once __DIR__ . '/../models/Ambiente.php';

            $todosConflictos = [];

            foreach ($horarios as $horario) {
                $resultado = Ambiente::verificarDisponibilidad(
                    $ambienteId,
                    $horario['fecha'],
                    $horario['hora_inicio'],
                    $horario['hora_fin'],
                    $asignacionId
                );

                if (!$resultado['disponible']) {
                    foreach ($resultado['conflictos'] as $conflicto) {
                        $todosConflictos[] = $conflicto;
                    }
                }
            }

            if (empty($todosConflictos)) {
                echo json_encode(['success' => true, 'disponible' => true]);
            } else {
                echo json_encode([
                    'success' => true,
                    'disponible' => false,
                    'conflictos' => $todosConflictos
                ]);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Verificar disponibilidad de instructor (conflictos de horarios)
     */
    function verificarDisponibilidadInstructor()
    {
        header('Content-Type: application/json');

        try {
            $instructorId = $_POST['inst_id'] ?? null;
            $horarios = json_decode($_POST['horarios'] ?? '[]', true);
            $asignacionId = $_POST['asig_id'] ?? null; // Para edición

            if (!$instructorId || !$horarios) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            require_once __DIR__ . '/../models/Instructor.php';

            $todosConflictos = [];

            foreach ($horarios as $horario) {
                $resultado = $this->verificarConflictoInstructor(
                    $instructorId,
                    $horario['fecha'],
                    $horario['hora_inicio'],
                    $horario['hora_fin'],
                    $asignacionId
                );

                if (!$resultado['disponible']) {
                    foreach ($resultado['conflictos'] as $conflicto) {
                        $todosConflictos[] = $conflicto;
                    }
                }
            }

            if (empty($todosConflictos)) {
                echo json_encode(['success' => true, 'disponible' => true]);
            } else {
                echo json_encode([
                    'success' => true,
                    'disponible' => false,
                    'conflictos' => $todosConflictos
                ]);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Verificar si un instructor tiene conflictos de horario
     */
    private function verificarConflictoInstructor($instructorId, $fecha, $horaInicio, $horaFin, $asignacionIdExcluir = null)
    {
        require_once __DIR__ . '/../Database.php';
        $db = Database::getInstance();

        // Consulta para verificar conflictos de horario del instructor
        // Busca cualquier asignación del mismo instructor en la misma fecha donde los horarios se solapen
        $sql = "SELECT a.ASIG_ID, a.FICHA_fich_id, da.detasig_fecha, da.detasig_hora_ini, da.detasig_hora_fin,
                       c.comp_nombre_corto, amb.amb_nombre
                FROM ASIGNACION a
                INNER JOIN DETALLE_ASIGNACION da ON a.ASIG_ID = da.ASIGNACION_ASIG_ID
                INNER JOIN COMPETENCIA c ON a.COMPETENCIA_comp_id = c.comp_id
                INNER JOIN AMBIENTE amb ON a.AMBIENTE_amb_id = amb.amb_id
                WHERE a.INSTRUCTOR_inst_id = ?
                AND da.detasig_fecha = ?
                AND (
                    (? < da.detasig_hora_fin AND ? > da.detasig_hora_ini)
                )";

        $params = [
            $instructorId,
            $fecha,
            $horaInicio,  // El nuevo horario inicia antes de que termine el existente
            $horaFin      // El nuevo horario termina después de que inicie el existente
        ];

        // Si es una edición, excluir la asignación actual
        if ($asignacionIdExcluir) {
            $sql .= " AND a.ASIG_ID != ?";
            $params[] = $asignacionIdExcluir;
        }

        $conflictos = $db->select($sql, $params);

        if (empty($conflictos)) {
            return ['disponible' => true, 'conflictos' => []];
        }

        // Formatear conflictos para mostrar información útil
        $conflictosFormateados = [];
        foreach ($conflictos as $conflicto) {
            $conflictosFormateados[] = [
                'asignacion_id' => $conflicto['ASIG_ID'],
                'ficha' => $conflicto['FICHA_fich_id'],
                'competencia' => $conflicto['comp_nombre_corto'],
                'ambiente' => $conflicto['amb_nombre'],
                'fecha' => $conflicto['detasig_fecha'],
                'hora_inicio' => substr($conflicto['detasig_hora_ini'], 0, 5),
                'hora_fin' => substr($conflicto['detasig_hora_fin'], 0, 5),
                'mensaje' => "Conflicto en {$conflicto['amb_nombre']} de " . 
                           substr($conflicto['detasig_hora_ini'], 0, 5) . " a " . 
                           substr($conflicto['detasig_hora_fin'], 0, 5) . 
                           " (Ficha: {$conflicto['FICHA_fich_id']}, Competencia: {$conflicto['comp_nombre_corto']})"
            ];
        }

        return [
            'disponible' => false,
            'conflictos' => $conflictosFormateados
        ];
    }

    function getAsignacionesInstructor()
    {
        header('Content-Type: application/json');

        try {
            $instructorId = $_GET['inst_id'] ?? null;

            if (!$instructorId) {
                echo json_encode(['success' => false, 'message' => 'ID de instructor no proporcionado']);
                return;
            }

            // Obtener todas las asignaciones del instructor
            require_once __DIR__ . '/../Database.php';
            $db = Database::getInstance();
            $sql = "SELECT a.ASIG_ID, a.FICHA_fich_id 
                    FROM ASIGNACION a
                    WHERE a.INSTRUCTOR_inst_id = ?";
            $asignaciones = $db->select($sql, [$instructorId]);

            $resultado = [];

            foreach ($asignaciones as $asig) {
                // Obtener detalles de cada asignación
                $detalles = DetalleAsignacion::searchByAsignacion($asig['ASIG_ID']);

                // Obtener información de la asignación completa
                $asignacion = Asignacion::find($asig['ASIG_ID']);
                $competencia = Competencia::find($asignacion->getCompetenciaId());
                $nombreCompetencia = $competencia ? $competencia->getNombreCorto() : 'Competencia';

                foreach ($detalles as $detalle) {
                    $resultado[] = [
                        'fecha' => $detalle->getFecha(),
                        'hora_inicio' => substr($detalle->getHoraInicio(), 0, 5),
                        'hora_fin' => substr($detalle->getHoraFin(), 0, 5),
                        'competencia' => $nombreCompetencia,
                        'ficha' => $asig['FICHA_fich_id'],
                        'asignacion_id' => $asig['ASIG_ID']
                    ];
                }
            }

            echo json_encode(['success' => true, 'asignaciones' => $resultado]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    function getAsignacionesAmbiente()
    {
        header('Content-Type: application/json');

        try {
            $ambienteId = $_GET['amb_id'] ?? null;

            if (!$ambienteId) {
                echo json_encode(['success' => false, 'message' => 'ID de ambiente no proporcionado']);
                return;
            }

            // Obtener todas las asignaciones del ambiente
            require_once __DIR__ . '/../Database.php';
            $db = Database::getInstance();
            $sql = "SELECT a.ASIG_ID, a.FICHA_fich_id 
                    FROM ASIGNACION a
                    WHERE a.AMBIENTE_amb_id = ?";
            $asignaciones = $db->select($sql, [$ambienteId]);

            $resultado = [];

            foreach ($asignaciones as $asig) {
                // Obtener detalles de cada asignación
                $detalles = DetalleAsignacion::searchByAsignacion($asig['ASIG_ID']);

                // Obtener información de la asignación completa
                $asignacion = Asignacion::find($asig['ASIG_ID']);
                $competencia = Competencia::find($asignacion->getCompetenciaId());
                $nombreCompetencia = $competencia ? $competencia->getNombreCorto() : 'Competencia';

                foreach ($detalles as $detalle) {
                    $resultado[] = [
                        'fecha' => $detalle->getFecha(),
                        'hora_inicio' => substr($detalle->getHoraInicio(), 0, 5),
                        'hora_fin' => substr($detalle->getHoraFin(), 0, 5),
                        'competencia' => $nombreCompetencia,
                        'ficha' => $asig['FICHA_fich_id'],
                        'asignacion_id' => $asig['ASIG_ID']
                    ];
                }
            }

            echo json_encode(['success' => true, 'asignaciones' => $resultado]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>