<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Detalle de Asignación</title>
</head>
<body>
    <h1>Editar Detalle de Asignación</h1>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $detalle->getId(); ?>">
        
        <label>Asignación:</label>
        <select name="asignacionId" required>
            <?php foreach($asignaciones as $asignacion): ?>
            <option value="<?php echo $asignacion->getId(); ?>" <?php echo $asignacion->getId() == $detalle->getAsignacionId() ? 'selected' : ''; ?>>
                <?php echo $asignacion->getId(); ?>
            </option>
            <?php endforeach; ?>
        </select><br><br>
        
        <label>Hora Inicio:</label>
        <input type="time" name="horaInicio" value="<?php echo $detalle->getHoraInicio(); ?>" required><br><br>
        
        <label>Hora Fin:</label>
        <input type="time" name="horaFin" value="<?php echo $detalle->getHoraFin(); ?>" required><br><br>
        
        <button type="submit">Actualizar</button>
        <a href="index.php">Cancelar</a>
    </form>
</body>
</html>
