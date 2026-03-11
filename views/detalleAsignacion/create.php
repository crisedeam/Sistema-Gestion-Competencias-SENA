<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Detalle de Asignación</title>
</head>
<body>
    <h1>Crear Nuevo Detalle de Asignación</h1>
    <form method="POST" action="">
        <label>Asignación:</label>
        <select name="asignacionId" required>
            <option value="">Seleccione una asignación</option>
            <?php foreach($asignaciones as $asignacion): ?>
            <option value="<?php echo $asignacion->getId(); ?>"><?php echo $asignacion->getId(); ?></option>
            <?php endforeach; ?>
        </select><br><br>
        
        <label>Hora Inicio:</label>
        <input type="time" name="horaInicio" required><br><br>
        
        <label>Hora Fin:</label>
        <input type="time" name="horaFin" required><br><br>
        
        <button type="submit">Guardar</button>
        <a href="index.php">Cancelar</a>
    </form>
</body>
</html>
