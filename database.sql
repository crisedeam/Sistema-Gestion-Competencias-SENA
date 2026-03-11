-- ============================================
-- Script de Base de Datos ProgSENA
-- Versión Final con Auditoría - Ejecutable en MySQL
-- ============================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ============================================
-- 1. CREAR/RECREAR BASE DE DATOS
-- ============================================
DROP DATABASE IF EXISTS ProgSENA;
CREATE DATABASE ProgSENA CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ProgSENA;

-- ============================================
-- 2. CREAR TABLAS PRINCIPALES
-- ============================================

-- Tabla: TITULO_PROGRAMA (Tecnologo, Tecnico, Auxiliar)
CREATE TABLE TITULO_PROGRAMA (
  titpro_id INT NOT NULL AUTO_INCREMENT,
  titpro_nombre VARCHAR(45) NOT NULL,
  PRIMARY KEY (titpro_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: PROGRAMA
CREATE TABLE PROGRAMA (
  prog_codigo INT NOT NULL,
  prog_denominacion VARCHAR(100) NOT NULL,
  TIT_PROGRAMA_titpro_id INT NOT NULL,
  prog_tipo VARCHAR(30) NOT NULL,
  PRIMARY KEY (prog_codigo),
  INDEX fk_PROGRAMA_TIPO_PROGRAMA_idx (TIT_PROGRAMA_titpro_id ASC),
  CONSTRAINT fk_PROGRAMA_TIPO_PROGRAMA
    FOREIGN KEY (TIT_PROGRAMA_titpro_id)
    REFERENCES TITULO_PROGRAMA (titpro_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: COMPETENCIA
CREATE TABLE COMPETENCIA (
  comp_id INT NOT NULL AUTO_INCREMENT,
  comp_nombre_corto VARCHAR(30) NOT NULL,
  comp_horas INT NOT NULL,
  comp_nombre_unidad_competencia VARCHAR(200) NOT NULL,
  PRIMARY KEY (comp_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: CENTRO_FORMACION
CREATE TABLE CENTRO_FORMACION (
  cent_id INT NOT NULL AUTO_INCREMENT,
  cent_nombre VARCHAR(100) NOT NULL,
  cent_correo VARCHAR(45) NOT NULL,
  cent_password VARCHAR(255) NOT NULL,
  PRIMARY KEY (cent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: COORDINACION
CREATE TABLE COORDINACION (
  coord_id INT NOT NULL AUTO_INCREMENT,
  coord_descripcion VARCHAR(45) NOT NULL,
  CENTRO_FORMACION_cent_id INT NOT NULL,
  coord_nombre_coordinador VARCHAR(45) NOT NULL,
  coord_correo VARCHAR(45) NOT NULL,
  coord_password VARCHAR(255) NOT NULL,
  PRIMARY KEY (coord_id),
  INDEX fk_COORDINACION_CENTRO_FORMACION1_idx (CENTRO_FORMACION_cent_id ASC),
  CONSTRAINT fk_COORDINACION_CENTRO_FORMACION1
    FOREIGN KEY (CENTRO_FORMACION_cent_id)
    REFERENCES CENTRO_FORMACION (cent_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: INSTRUCTOR
CREATE TABLE INSTRUCTOR (
  inst_id INT NOT NULL AUTO_INCREMENT,
  inst_nombres VARCHAR(45) NOT NULL,
  inst_apellidos VARCHAR(45) NOT NULL,
  inst_correo VARCHAR(45) NOT NULL,
  inst_telefono BIGINT NOT NULL,
  CENTRO_FORMACION_cent_id INT NOT NULL,
  inst_password VARCHAR(255) NOT NULL,
  PRIMARY KEY (inst_id),
  INDEX fk_INSTRUCTOR_CENTRO_FORMACION1_idx (CENTRO_FORMACION_cent_id ASC),
  CONSTRAINT fk_INSTRUCTOR_CENTRO_FORMACION1
    FOREIGN KEY (CENTRO_FORMACION_cent_id)
    REFERENCES CENTRO_FORMACION (cent_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: COMPETENCIA_PROGRAMA
CREATE TABLE COMPETENCIA_PROGRAMA (
  PROGRAMA_prog_id INT NOT NULL,
  COMPETENCIA_comp_id INT NOT NULL,
  PRIMARY KEY (PROGRAMA_prog_id, COMPETENCIA_comp_id),
  INDEX fk_COMPETENCIA_PROGRAMA_COMPETENCIA1_idx (COMPETENCIA_comp_id ASC),
  CONSTRAINT fk_COMPETENCIA_PROGRAMA_PROGRAMA1
    FOREIGN KEY (PROGRAMA_prog_id)
    REFERENCES PROGRAMA (prog_codigo)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_COMPETENCIA_PROGRAMA_COMPETENCIA1
    FOREIGN KEY (COMPETENCIA_comp_id)
    REFERENCES COMPETENCIA (comp_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: FICHA
CREATE TABLE FICHA (
  fich_id INT NOT NULL,
  PROGRAMA_prog_id INT NOT NULL,
  INSTRUCTOR_inst_id_lider INT NOT NULL,
  fich_jornada VARCHAR(20) NOT NULL,
  COORDINACION_coord_id INT NOT NULL,
  fich_fecha_ini_lectiva DATE NOT NULL,
  fich_fecha_fin_lectiva DATE NOT NULL,
  PRIMARY KEY (fich_id),
  INDEX fk_FICHA_PROGRAMA1_idx (PROGRAMA_prog_id ASC),
  INDEX fk_FICHA_INSTRUCTOR1_idx (INSTRUCTOR_inst_id_lider ASC),
  INDEX fk_FICHA_COORDINACION1_idx (COORDINACION_coord_id ASC),
  CONSTRAINT fk_FICHA_PROGRAMA1
    FOREIGN KEY (PROGRAMA_prog_id)
    REFERENCES PROGRAMA (prog_codigo)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_FICHA_INSTRUCTOR1
    FOREIGN KEY (INSTRUCTOR_inst_id_lider)
    REFERENCES INSTRUCTOR (inst_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_FICHA_COORDINACION1
    FOREIGN KEY (COORDINACION_coord_id)
    REFERENCES COORDINACION (coord_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: SEDE
CREATE TABLE SEDE (
  sede_id INT NOT NULL AUTO_INCREMENT,
  sede_nombre VARCHAR(45) NOT NULL,
  PRIMARY KEY (sede_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: AMBIENTE
CREATE TABLE AMBIENTE (
  amb_id VARCHAR(5) NOT NULL,
  amb_nombre VARCHAR(45) NULL,
  SEDE_sede_id INT NOT NULL,
  PRIMARY KEY (amb_id),
  INDEX fk_AMBIENTE_SEDE1_idx (SEDE_sede_id ASC),
  CONSTRAINT fk_AMBIENTE_SEDE1
    FOREIGN KEY (SEDE_sede_id)
    REFERENCES SEDE (sede_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ASIGNACION
CREATE TABLE ASIGNACION (
  ASIG_ID INT NOT NULL AUTO_INCREMENT,
  INSTRUCTOR_inst_id INT NOT NULL,
  asig_fecha_ini DATETIME NOT NULL,
  asig_fecha_fin DATETIME NOT NULL,
  FICHA_fich_id INT NOT NULL,
  AMBIENTE_amb_id VARCHAR(5) NOT NULL,
  COMPETENCIA_comp_id INT NOT NULL,
  PRIMARY KEY (ASIG_ID),
  INDEX fk_ASIGNACION_INSTRUCTOR1_idx (INSTRUCTOR_inst_id ASC),
  INDEX fk_ASIGNACION_FICHA1_idx (FICHA_fich_id ASC),
  INDEX fk_ASIGNACION_AMBIENTE1_idx (AMBIENTE_amb_id ASC),
  INDEX fk_ASIGNACION_COMPETENCIA1_idx (COMPETENCIA_comp_id ASC),
  CONSTRAINT fk_ASIGNACION_INSTRUCTOR1
    FOREIGN KEY (INSTRUCTOR_inst_id)
    REFERENCES INSTRUCTOR (inst_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_ASIGNACION_FICHA1
    FOREIGN KEY (FICHA_fich_id)
    REFERENCES FICHA (fich_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_ASIGNACION_AMBIENTE1
    FOREIGN KEY (AMBIENTE_amb_id)
    REFERENCES AMBIENTE (amb_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_ASIGNACION_COMPETENCIA1
    FOREIGN KEY (COMPETENCIA_comp_id)
    REFERENCES COMPETENCIA (comp_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: DETALLE_ASIGNACION
CREATE TABLE DETALLE_ASIGNACION (
  detasig_id INT NOT NULL AUTO_INCREMENT,
  ASIGNACION_ASIG_ID INT NOT NULL,
  detasig_fecha DATE NOT NULL,
  detasig_hora_ini TIME NOT NULL,
  detasig_hora_fin TIME NOT NULL,
  PRIMARY KEY (detasig_id),
  INDEX fk_DETALLE_ASIGNACION_ASIGNACION1_idx (ASIGNACION_ASIG_ID ASC),
  CONSTRAINT fk_DETALLE_ASIGNACION_ASIGNACION1
    FOREIGN KEY (ASIGNACION_ASIG_ID)
    REFERENCES ASIGNACION (ASIG_ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA DE AUDITORÍA PARA ASIGNACION
-- ============================================

-- Tabla: AUDITORIA_ASIGNACION
CREATE TABLE AUDITORIA_ASIGNACION (
  audit_id INT NOT NULL AUTO_INCREMENT,
  asig_id INT NOT NULL,
  datos_anteriores JSON NULL COMMENT 'Datos antes del cambio (NULL para INSERT)',
  datos_nuevos JSON NULL COMMENT 'Datos después del cambio (NULL para DELETE)',
  audit_fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la acción',
  audit_usuario_correo VARCHAR(45) NOT NULL COMMENT 'Correo del usuario que realizó la acción',
  audit_accion ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL COMMENT 'Tipo de operación realizada',
  PRIMARY KEY (audit_id),
  INDEX idx_asig_id (asig_id),
  INDEX idx_fecha_hora (audit_fecha_hora),
  INDEX idx_accion (audit_accion),
  INDEX idx_usuario (audit_usuario_correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabla de auditoría para registrar cambios en ASIGNACION';

-- Tabla: INSTRUCTOR_COMPETENCIA
CREATE TABLE INSTRUCTOR_COMPETENCIA (
  inscomp_id INT NOT NULL AUTO_INCREMENT,
  INSTRUCTOR_inst_id INT NOT NULL,
  COMPETENCIA_PROGRAMA_PROGRAMA_prog_id INT NOT NULL,
  COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id INT NOT NULL,
  inscomp_vigencia DATE NOT NULL,
  PRIMARY KEY (inscomp_id),
  INDEX fk_INSTRUCTOR_COMPETENCIA_INSTRUCTOR1_idx (INSTRUCTOR_inst_id ASC),
  INDEX fk_INSTRUCTOR_COMPETENCIA_COMPETENCIA_PROGRAMA1_idx (COMPETENCIA_PROGRAMA_PROGRAMA_prog_id ASC, COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id ASC),
  CONSTRAINT fk_INSTRUCTOR_COMPETENCIA_INSTRUCTOR1
    FOREIGN KEY (INSTRUCTOR_inst_id)
    REFERENCES INSTRUCTOR (inst_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_INSTRUCTOR_COMPETENCIA_COMPETENCIA_PROGRAMA1
    FOREIGN KEY (COMPETENCIA_PROGRAMA_PROGRAMA_prog_id, COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id)
    REFERENCES COMPETENCIA_PROGRAMA (PROGRAMA_prog_id, COMPETENCIA_comp_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGERS DE AUDITORÍA PARA ASIGNACION
-- ============================================

-- Trigger para INSERT: Registra nuevas asignaciones
DELIMITER $$
CREATE TRIGGER trg_auditoria_asignacion_insert
AFTER INSERT ON ASIGNACION
FOR EACH ROW
BEGIN
  INSERT INTO AUDITORIA_ASIGNACION (
    asig_id,
    datos_anteriores,
    datos_nuevos,
    audit_fecha_hora,
    audit_usuario_correo,
    audit_accion
  ) VALUES (
    NEW.ASIG_ID,
    NULL,
    JSON_OBJECT(
      'INSTRUCTOR_inst_id', NEW.INSTRUCTOR_inst_id,
      'asig_fecha_ini', NEW.asig_fecha_ini,
      'asig_fecha_fin', NEW.asig_fecha_fin,
      'FICHA_fich_id', NEW.FICHA_fich_id,
      'AMBIENTE_amb_id', NEW.AMBIENTE_amb_id,
      'COMPETENCIA_comp_id', NEW.COMPETENCIA_comp_id
    ),
    NOW(),
    IFNULL(@usuario_correo, 'sistema@sena.edu.co'),
    'INSERT'
  );
END$$
DELIMITER ;

-- Trigger para UPDATE: Registra modificaciones
DELIMITER $$
CREATE TRIGGER trg_auditoria_asignacion_update
AFTER UPDATE ON ASIGNACION
FOR EACH ROW
BEGIN
  INSERT INTO AUDITORIA_ASIGNACION (
    asig_id,
    datos_anteriores,
    datos_nuevos,
    audit_fecha_hora,
    audit_usuario_correo,
    audit_accion
  ) VALUES (
    NEW.ASIG_ID,
    JSON_OBJECT(
      'INSTRUCTOR_inst_id', OLD.INSTRUCTOR_inst_id,
      'asig_fecha_ini', OLD.asig_fecha_ini,
      'asig_fecha_fin', OLD.asig_fecha_fin,
      'FICHA_fich_id', OLD.FICHA_fich_id,
      'AMBIENTE_amb_id', OLD.AMBIENTE_amb_id,
      'COMPETENCIA_comp_id', OLD.COMPETENCIA_comp_id
    ),
    JSON_OBJECT(
      'INSTRUCTOR_inst_id', NEW.INSTRUCTOR_inst_id,
      'asig_fecha_ini', NEW.asig_fecha_ini,
      'asig_fecha_fin', NEW.asig_fecha_fin,
      'FICHA_fich_id', NEW.FICHA_fich_id,
      'AMBIENTE_amb_id', NEW.AMBIENTE_amb_id,
      'COMPETENCIA_comp_id', NEW.COMPETENCIA_comp_id
    ),
    NOW(),
    IFNULL(@usuario_correo, 'sistema@sena.edu.co'),
    'UPDATE'
  );
END$$
DELIMITER ;

-- Trigger para DELETE: Registra eliminaciones
DELIMITER $$
CREATE TRIGGER trg_auditoria_asignacion_delete
BEFORE DELETE ON ASIGNACION
FOR EACH ROW
BEGIN
  INSERT INTO AUDITORIA_ASIGNACION (
    asig_id,
    datos_anteriores,
    datos_nuevos,
    audit_fecha_hora,
    audit_usuario_correo,
    audit_accion
  ) VALUES (
    OLD.ASIG_ID,
    JSON_OBJECT(
      'INSTRUCTOR_inst_id', OLD.INSTRUCTOR_inst_id,
      'asig_fecha_ini', OLD.asig_fecha_ini,
      'asig_fecha_fin', OLD.asig_fecha_fin,
      'FICHA_fich_id', OLD.FICHA_fich_id,
      'AMBIENTE_amb_id', OLD.AMBIENTE_amb_id,
      'COMPETENCIA_comp_id', OLD.COMPETENCIA_comp_id
    ),
    NULL,
    NOW(),
    IFNULL(@usuario_correo, 'sistema@sena.edu.co'),
    'DELETE'
  );
END$$
DELIMITER ;

-- ============================================
-- 3. INSERTAR DATOS DE EJEMPLO
-- ============================================
-- NOTA: No se incluyen datos de ejemplo para ASIGNACION ni DETALLE_ASIGNACION
--       Estas tablas deben ser llenadas a través de la aplicación

-- Insertar Títulos de Programa
INSERT INTO TITULO_PROGRAMA (titpro_nombre) VALUES 
('Técnico'),
('Tecnólogo'),
('Especialización Tecnológica'),
('Curso Complementario');

-- Insertar Programas
INSERT INTO PROGRAMA (prog_codigo, prog_denominacion, TIT_PROGRAMA_titpro_id, prog_tipo) VALUES 
(228106, 'Análisis y Desarrollo de Software', 2, 'Tecnólogo'),
(228118, 'Gestión de Redes de Datos', 2, 'Tecnólogo'),
(228120, 'Diseño Gráfico', 2, 'Tecnólogo'),
(228130, 'Gestión Empresarial', 2, 'Tecnólogo'),
(228140, 'Producción Biotecnológica', 2, 'Tecnólogo'),
(934108, 'Sistemas', 1, 'Técnico'),
(934120, 'Mantenimiento de Equipos de Cómputo', 1, 'Técnico'),
(934130, 'Mecánica Automotriz', 1, 'Técnico'),
(934140, 'Soldadura', 1, 'Técnico'),
(934150, 'Servicios Turísticos', 1, 'Técnico');

-- Insertar Competencias (Transversales y Específicas)
INSERT INTO COMPETENCIA (comp_nombre_corto, comp_horas, comp_nombre_unidad_competencia) VALUES 
-- Competencias Transversales (1-8)
('ETICA', 48, 'Promover la interacción idónea consigo mismo, con los demás y con la naturaleza en los contextos laboral y social'),
('COMUNICACION', 48, 'Comprender textos en inglés en forma escrita y auditiva'),
('EMPRENDIMIENTO', 48, 'Aplicar en la resolución de problemas reales del sector productivo, los conocimientos, habilidades y destrezas pertinentes a las competencias del programa de formación'),
('CULTURA_FISICA', 48, 'Desarrollar permanentemente las habilidades psicomotrices y de pensamiento en la ejecución de los procesos de aprendizaje'),
('TECNOLOGIAS', 48, 'Aplicar tecnologías de la información teniendo en cuenta las necesidades de la unidad productiva'),
('MEDIO_AMBIENTE', 48, 'Generar procesos autónomos y de trabajo colaborativo permanentes, fortaleciendo el equilibrio de los componentes racionales y emocionales orientados hacia el desarrollo humano integral'),
('LIDERAZGO', 48, 'Ejercer derechos fundamentales del trabajo en el marco de la Constitución Política y los convenios internacionales'),
('INVESTIGACION', 48, 'Desarrollar procesos de investigación aplicada en el área de conocimiento del programa de formación'),
-- Competencias Específicas ADSO (9-14)
('PROG_BASICA', 120, 'Implementar la estructura de la base de datos a partir del diseño establecido'),
('PROG_ORIENTADA', 180, 'Desarrollar el sistema que cumpla con los requisitos de la solución informática'),
('BASES_DATOS', 160, 'Construir el sistema que cumpla con los requisitos de la solución informática'),
('ANALISIS_DISEÑO', 200, 'Analizar los requisitos del cliente para construir el sistema de información'),
('PRUEBAS_SOFTWARE', 100, 'Realizar mantenimiento de la solución informática de acuerdo con las necesidades del cliente'),
('DESARROLLO_WEB', 140, 'Implementar la interfaz de usuario de acuerdo con el diseño establecido'),
-- Competencias Específicas Redes (15-18)
('REDES_BASICAS', 160, 'Implementar la estructura de la red de acuerdo con un diseño preestablecido'),
('REDES_AVANZADAS', 180, 'Participar en el proceso de diagnóstico y solución de fallas en la red'),
('SEGURIDAD_REDES', 140, 'Implementar seguridad en la red de acuerdo con políticas establecidas'),
('ADMIN_SERVIDORES', 120, 'Administrar los recursos de la red de acuerdo con requerimientos del cliente'),
-- Competencias Específicas Diseño Gráfico (19-22)
('DISEÑO_DIGITAL', 160, 'Desarrollar piezas gráficas de acuerdo con las necesidades del cliente'),
('ILUSTRACION', 120, 'Crear ilustraciones digitales aplicando técnicas de dibujo'),
('FOTOGRAFIA', 100, 'Realizar toma y edición fotográfica de acuerdo con requerimientos del proyecto'),
('ANIMACION', 140, 'Desarrollar animaciones digitales aplicando principios de movimiento'),
-- Competencias Específicas Gestión Empresarial (23-26)
('CONTABILIDAD', 140, 'Contabilizar los recursos de operación, inversión y financiación de acuerdo con las normas vigentes'),
('MARKETING', 120, 'Planear actividades de mercadeo que respondan a las necesidades y expectativas de los clientes'),
('TALENTO_HUMANO', 100, 'Facilitar el servicio a los clientes internos y externos de acuerdo con las políticas de la organización'),
('FINANZAS', 120, 'Proponer alternativas de solución que contribuyan al logro de los objetivos de acuerdo con el nivel de importancia'),
-- Competencias Específicas Biotecnología (27-30)
('MICROBIOLOGIA', 160, 'Realizar análisis microbiológicos según protocolos establecidos'),
('BIOQUIMICA', 140, 'Ejecutar procesos bioquímicos de acuerdo con procedimientos técnicos'),
('CONTROL_CALIDAD', 120, 'Controlar procesos biotecnológicos según parámetros de calidad establecidos'),
('BIOSEGURIDAD', 100, 'Aplicar normas de bioseguridad en procesos biotecnológicos'),
-- Competencias Específicas Sistemas Técnico (31-33)
('MANTENIMIENTO_PC', 120, 'Realizar mantenimiento preventivo y correctivo de equipos de cómputo'),
('INSTALACION_SO', 100, 'Instalar y configurar sistemas operativos según requerimientos'),
('SOPORTE_TECNICO', 80, 'Brindar soporte técnico a usuarios de acuerdo con procedimientos establecidos'),
-- Competencias Específicas Mecánica Automotriz (34-36)
('MOTORES', 140, 'Realizar mantenimiento de motores de combustión interna según especificaciones técnicas'),
('SISTEMAS_ELECTRICOS', 120, 'Diagnosticar y reparar sistemas eléctricos y electrónicos del vehículo'),
('TRANSMISION', 100, 'Realizar mantenimiento de sistemas de transmisión según normas del fabricante'),
-- Competencias Específicas Soldadura (37-39)
('SOLDADURA_ELECTRICA', 140, 'Aplicar procesos de soldadura por arco eléctrico según procedimientos'),
('SOLDADURA_MIG', 120, 'Realizar uniones soldadas con proceso MIG según especificaciones técnicas'),
('LECTURA_PLANOS', 80, 'Interpretar planos de fabricación y montaje de estructuras metálicas'),
-- Competencias Específicas Servicios Turísticos (40-42)
('ATENCION_CLIENTE', 100, 'Brindar atención y servicio al cliente de acuerdo con estándares de calidad'),
('GUIANZA_TURISTICA', 120, 'Realizar guianza turística aplicando técnicas de interpretación del patrimonio'),
('OPERACION_TURISTICA', 100, 'Operar servicios turísticos de acuerdo con la normatividad vigente');

-- Insertar Centros de Formación
-- Password por defecto: password123
INSERT INTO CENTRO_FORMACION (cent_nombre, cent_correo, cent_password) VALUES 
('Centro de Biotecnología Agropecuaria', 'biotecnologia@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Centro de Servicios Empresariales y Turísticos', 'empresariales@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Centro de Tecnologías del Transporte', 'transporte@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Centro de Diseño e Innovación Tecnológica Industrial', 'diseno@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Centro de Gestión de Mercados, Logística y TIC', 'tic@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insertar Coordinaciones
INSERT INTO COORDINACION (coord_descripcion, CENTRO_FORMACION_cent_id, coord_nombre_coordinador, coord_correo, coord_password) VALUES 
('Coordinación Académica', 1, 'María Elena González', 'maria.gonzalez@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Coordinación Académica', 2, 'Carlos Alberto Martínez', 'carlos.martinez@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Coordinación Académica', 3, 'Ana Patricia Hernández', 'ana.hernandez@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Coordinación Académica', 4, 'Luis Fernando Ramírez', 'luis.ramirez@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Coordinación Académica', 5, 'Sandra Milena Torres', 'sandra.torres@sena.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insertar Instructores
INSERT INTO INSTRUCTOR (inst_nombres, inst_apellidos, inst_correo, inst_telefono, CENTRO_FORMACION_cent_id, inst_password) VALUES 
('Juan Carlos', 'Pérez Morales', 'juan.perez@sena.edu.co', 3101111001, 1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Diana Patricia', 'Ruiz Gómez', 'diana.ruiz@sena.edu.co', 3101111002, 1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Roberto', 'Sánchez Díaz', 'roberto.sanchez@sena.edu.co', 3101111003, 1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Carmen Lucía', 'García Mendoza', 'carmen.garcia@sena.edu.co', 3201111004, 2, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Miguel Ángel', 'López Herrera', 'miguel.lopez@sena.edu.co', 3201111005, 2, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Claudia Marcela', 'Jiménez Rojas', 'claudia.jimenez@sena.edu.co', 3201111006, 2, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Andrés Felipe', 'Moreno Castillo', 'andres.moreno@sena.edu.co', 3301111007, 3, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Paola Andrea', 'Vásquez Torres', 'paola.vasquez@sena.edu.co', 3301111008, 3, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Julián David', 'Ramírez Peña', 'julian.ramirez@sena.edu.co', 3401111009, 4, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Natalia', 'Ospina Cardona', 'natalia.ospina@sena.edu.co', 3401111010, 4, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Sebastián', 'Gutiérrez Álvarez', 'sebastian.gutierrez@sena.edu.co', 3501111011, 5, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Alejandra', 'Muñoz Salazar', 'alejandra.munoz@sena.edu.co', 3501111012, 5, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Daniel Eduardo', 'Vargas Quintero', 'daniel.vargas@sena.edu.co', 3501111013, 5, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Mónica Patricia', 'Restrepo Gil', 'monica.restrepo@sena.edu.co', 3501111014, 5, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insertar relación Competencia-Programa
INSERT INTO COMPETENCIA_PROGRAMA (PROGRAMA_prog_id, COMPETENCIA_comp_id) VALUES 
-- Programa 228106 (Análisis y Desarrollo de Software) - Transversales + Específicas
(228106, 1), (228106, 2), (228106, 3), (228106, 4), (228106, 5), (228106, 6), (228106, 7), (228106, 8),
(228106, 9), (228106, 10), (228106, 11), (228106, 12), (228106, 13), (228106, 14),
-- Programa 228118 (Gestión de Redes) - Transversales + Específicas
(228118, 1), (228118, 2), (228118, 3), (228118, 4), (228118, 5), (228118, 6), (228118, 7), (228118, 8),
(228118, 15), (228118, 16), (228118, 17), (228118, 18),
-- Programa 228120 (Diseño Gráfico) - Transversales + Específicas
(228120, 1), (228120, 2), (228120, 3), (228120, 4), (228120, 5), (228120, 6), (228120, 7), (228120, 8),
(228120, 19), (228120, 20), (228120, 21), (228120, 22),
-- Programa 228130 (Gestión Empresarial) - Transversales + Específicas
(228130, 1), (228130, 2), (228130, 3), (228130, 4), (228130, 5), (228130, 6), (228130, 7), (228130, 8),
(228130, 23), (228130, 24), (228130, 25), (228130, 26),
-- Programa 228140 (Producción Biotecnológica) - Transversales + Específicas
(228140, 1), (228140, 2), (228140, 3), (228140, 4), (228140, 5), (228140, 6), (228140, 7), (228140, 8),
(228140, 27), (228140, 28), (228140, 29), (228140, 30),
-- Programa 934108 (Sistemas) - Transversales + Específicas
(934108, 1), (934108, 2), (934108, 3), (934108, 4), (934108, 5), (934108, 6),
(934108, 31), (934108, 32), (934108, 33),
-- Programa 934120 (Mantenimiento de Equipos) - Transversales + Específicas
(934120, 1), (934120, 2), (934120, 3), (934120, 4), (934120, 5), (934120, 6),
(934120, 31), (934120, 32), (934120, 33),
-- Programa 934130 (Mecánica Automotriz) - Transversales + Específicas
(934130, 1), (934130, 2), (934130, 3), (934130, 4), (934130, 5), (934130, 6),
(934130, 34), (934130, 35), (934130, 36),
-- Programa 934140 (Soldadura) - Transversales + Específicas
(934140, 1), (934140, 2), (934140, 3), (934140, 4), (934140, 5), (934140, 6),
(934140, 37), (934140, 38), (934140, 39),
-- Programa 934150 (Servicios Turísticos) - Transversales + Específicas
(934150, 1), (934150, 2), (934150, 3), (934150, 4), (934150, 5), (934150, 6),
(934150, 40), (934150, 41), (934150, 42);

-- Insertar Fichas
INSERT INTO FICHA (fich_id, PROGRAMA_prog_id, INSTRUCTOR_inst_id_lider, fich_jornada, COORDINACION_coord_id, fich_fecha_ini_lectiva, fich_fecha_fin_lectiva) VALUES 
(2558963, 228106, 11, 'Mañana', 5, '2026-03-01', '2027-08-31'),
(2558964, 228118, 12, 'Mañana', 5, '2026-03-01', '2027-08-31'),
(2558965, 934108, 13, 'Mañana', 5, '2026-03-01', '2026-12-31'),
(2558966, 228120, 9, 'Tarde', 4, '2026-03-01', '2027-08-31'),
(2558967, 228130, 4, 'Tarde', 2, '2026-03-01', '2027-08-31'),
(2558968, 934150, 5, 'Tarde', 2, '2026-03-01', '2026-12-31'),
(2558969, 228106, 14, 'Noche', 5, '2026-03-01', '2027-08-31'),
(2558970, 934130, 7, 'Noche', 3, '2026-03-01', '2026-12-31'),
(2558971, 934140, 8, 'Noche', 3, '2026-03-01', '2026-12-31'),
(2558972, 228140, 1, 'Mixta', 1, '2026-03-01', '2027-08-31'),
(2558973, 934120, 2, 'Mixta', 1, '2026-03-01', '2026-12-31');

-- Insertar Sedes
INSERT INTO SEDE (sede_nombre) VALUES 
('Sede Principal'),
('Sede Norte'),
('Sede Sur'),
('Sede Oriente'),
('Sede Occidente');

-- Insertar Ambientes
INSERT INTO AMBIENTE (amb_id, amb_nombre, SEDE_sede_id) VALUES 
('A101', 'Laboratorio de Sistemas 1', 1),
('A102', 'Laboratorio de Sistemas 2', 1),
('A103', 'Laboratorio de Redes', 1),
('A104', 'Aula Teórica 1', 1),
('A105', 'Aula Teórica 2', 1),
('B201', 'Laboratorio de Diseño', 2),
('B202', 'Taller de Mecánica', 2),
('B203', 'Aula Multimedial', 2),
('C301', 'Laboratorio de Biotecnología', 3),
('C302', 'Sala de Conferencias', 3),
('D401', 'Taller de Soldadura', 4),
('D402', 'Laboratorio de Electrónica', 4),
('E501', 'Aula de Servicios', 5),
('E502', 'Laboratorio de Turismo', 5);

-- Insertar relaciones Instructor-Competencia
INSERT INTO INSTRUCTOR_COMPETENCIA (INSTRUCTOR_inst_id, COMPETENCIA_PROGRAMA_PROGRAMA_prog_id, COMPETENCIA_PROGRAMA_COMPETENCIA_comp_id, inscomp_vigencia) VALUES 
-- Instructor 1: Juan Carlos (Biotecnología)
(1, 228140, 1, '2027-12-31'), (1, 228140, 2, '2027-12-31'), (1, 228140, 27, '2027-12-31'), (1, 228140, 28, '2027-12-31'), (1, 228140, 30, '2027-12-31'),
-- Instructor 2: Diana Patricia (Biotecnología)
(2, 228140, 1, '2027-12-31'), (2, 228140, 3, '2027-12-31'), (2, 228140, 27, '2027-12-31'), (2, 228140, 29, '2027-12-31'),
-- Instructor 3: Roberto (Biotecnología)
(3, 228140, 4, '2027-12-31'), (3, 228140, 28, '2027-12-31'), (3, 228140, 29, '2027-12-31'), (3, 228140, 30, '2027-12-31'),
-- Instructor 4: Carmen Lucía (Gestión Empresarial)
(4, 228130, 1, '2027-12-31'), (4, 228130, 3, '2027-12-31'), (4, 228130, 23, '2027-12-31'), (4, 228130, 24, '2027-12-31'), (4, 228130, 26, '2027-12-31'),
-- Instructor 5: Miguel Ángel (Servicios Turísticos)
(5, 934150, 1, '2027-12-31'), (5, 934150, 2, '2027-12-31'), (5, 934150, 40, '2027-12-31'), (5, 934150, 41, '2027-12-31'), (5, 934150, 42, '2027-12-31'),
-- Instructor 6: Claudia Marcela (Gestión Empresarial)
(6, 228130, 4, '2027-12-31'), (6, 228130, 23, '2027-12-31'), (6, 228130, 25, '2027-12-31'), (6, 228130, 26, '2027-12-31'),
-- Instructor 7: Andrés Felipe (Mecánica Automotriz)
(7, 934130, 1, '2027-12-31'), (7, 934130, 2, '2027-12-31'), (7, 934130, 34, '2027-12-31'), (7, 934130, 35, '2027-12-31'), (7, 934130, 36, '2027-12-31'),
-- Instructor 8: Paola Andrea (Soldadura)
(8, 934140, 1, '2027-12-31'), (8, 934140, 3, '2027-12-31'), (8, 934140, 37, '2027-12-31'), (8, 934140, 38, '2027-12-31'), (8, 934140, 39, '2027-12-31'),
-- Instructor 9: Julián David (Diseño Gráfico)
(9, 228120, 1, '2027-12-31'), (9, 228120, 5, '2027-12-31'), (9, 228120, 19, '2027-12-31'), (9, 228120, 20, '2027-12-31'), (9, 228120, 22, '2027-12-31'),
-- Instructor 10: Natalia (Diseño Gráfico)
(10, 228120, 2, '2027-12-31'), (10, 228120, 19, '2027-12-31'), (10, 228120, 21, '2027-12-31'), (10, 228120, 22, '2027-12-31'),
-- Instructor 11: Sebastián (ADSO)
(11, 228106, 1, '2027-12-31'), (11, 228106, 5, '2027-12-31'), (11, 228106, 9, '2027-12-31'), (11, 228106, 10, '2027-12-31'), (11, 228106, 11, '2027-12-31'), (11, 228106, 14, '2027-12-31'),
-- Instructor 12: Alejandra (Redes)
(12, 228118, 1, '2027-12-31'), (12, 228118, 5, '2027-12-31'), (12, 228118, 15, '2027-12-31'), (12, 228118, 16, '2027-12-31'), (12, 228118, 17, '2027-12-31'),
-- Instructor 13: Daniel (Sistemas Técnico)
(13, 934108, 1, '2027-12-31'), (13, 934108, 5, '2027-12-31'), (13, 934108, 31, '2027-12-31'), (13, 934108, 32, '2027-12-31'), (13, 934108, 33, '2027-12-31'),
-- Instructor 14: Mónica (ADSO)
(14, 228106, 1, '2027-12-31'), (14, 228106, 3, '2027-12-31'), (14, 228106, 9, '2027-12-31'), (14, 228106, 12, '2027-12-31'), (14, 228106, 13, '2027-12-31'), (14, 228106, 14, '2027-12-31');

-- ============================================
-- 4. RESTAURAR CONFIGURACIONES
-- ============================================
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
-- NOTA: Las tablas ASIGNACION, DETALLE_ASIGNACION y AUDITORIA_ASIGNACION
--       están vacías y listas para ser utilizadas por la aplicación.
--       Los triggers de auditoría están activos y registrarán automáticamente
--       todas las operaciones INSERT, UPDATE y DELETE en ASIGNACION.
