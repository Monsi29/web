<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
include 'db_connect.php';

$fecha_inicio = '';
$fecha_fin = '';
$grupo_id = '';
$reporte_result = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $grupo_id = $_POST['grupo_id'];

    $sql = "SELECT estudiantes.nombre, asistencia.fecha, asistencia.asistio 
            FROM asistencia 
            JOIN estudiantes ON asistencia.estudiante_id = estudiantes.id 
            WHERE estudiantes.grupo_id = ? AND asistencia.fecha BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $grupo_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $reporte_result = $stmt->get_result();
}

// Consulta para obtener los grupos
$sql_grupos = "SELECT id, nombre_grupo FROM grupos";
$result_grupos = $conn->query($sql_grupos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo del Sistema">
        </div>
        <h1>Reporte de Asistencia</h1>
        <a href="index.php" class="button">Inicio</a>
    </header>
    <main>
        <section>
            <h2>Generar Reporte</h2>
            <form method="POST" action="ver_reportes.php">
                <label for="grupo_id">Seleccionar Grupo:</label>
                <select name="grupo_id" id="grupo_id" required>
                    <option value="">Selecciona un grupo</option>
                    <?php while($row_grupo = $result_grupos->fetch_assoc()): ?>
                        <option value="<?php echo $row_grupo['id']; ?>" <?php if ($grupo_id == $row_grupo['id']) echo 'selected'; ?>>
                            <?php echo $row_grupo['nombre_grupo']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $fecha_inicio; ?>" required>

                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo $fecha_fin; ?>" required>

                <input type="submit" value="Generar Reporte">
            </form>
        </section>

        <section>
            <h2>Resultados del Reporte</h2>
            <?php if ($reporte_result && $reporte_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fecha</th>
                            <th>Asistió</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $reporte_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                <td><?php echo $row['asistio'] ? 'Sí' : 'No'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No se encontraron registros para el rango de fechas y grupo seleccionado.</p>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Sistema de Asistencia. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>