<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
include 'db_connect.php';

// Obtener el grupo seleccionado del formulario
$grupo_id = isset($_POST['grupo']) ? $_POST['grupo'] : '';

// Consulta para obtener los grupos
$sql_grupos = "SELECT id, nombre_grupo FROM grupos";
$result_grupos = $conn->query($sql_grupos);

// Consulta para obtener estudiantes según el grupo seleccionado
if ($grupo_id) {
    $sql_estudiantes = "SELECT * FROM estudiantes WHERE grupo_id = ?";
    $stmt = $conn->prepare($sql_estudiantes);
    $stmt->bind_param('i', $grupo_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
} else {
    // Si no se selecciona ningún grupo, no se muestran estudiantes
    $students_result = $conn->query("SELECT * FROM estudiantes WHERE 1=0");
}

// Manejo del registro de asistencia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_attendance'])) {
    $fecha = $_POST['fecha'];

    foreach ($_POST['attendance'] as $estudiante_id => $asistio) {
        $sql = "INSERT INTO asistencia (estudiante_id, fecha, asistio) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $estudiante_id, $fecha, $asistio);
        $stmt->execute();
    }
    echo "<p>Asistencia registrada exitosamente.</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Asistencia</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo del Sistema">
        </div>
        <h1>Registrar Asistencia</h1>
        <a href="index.php" class="button">Inicio</a>
    </header>
    <main>
        <section>
            <h2>Seleccionar Grupo</h2>
            <!-- Formulario para seleccionar el grupo -->
            <form method="POST" action="registrar_asistencia.php">
                <label for="grupo">Seleccionar Grupo:</label>
                <select name="grupo" id="grupo" onchange="this.form.submit()">
                    <option value="">Selecciona un grupo</option>
                    <?php while($row_grupo = $result_grupos->fetch_assoc()): ?>
                        <option value="<?php echo $row_grupo['id']; ?>" <?php if ($grupo_id == $row_grupo['id']) echo 'selected'; ?>>
                            <?php echo $row_grupo['nombre_grupo']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </section>

        <section>
            <h2>Registrar Asistencia</h2>
            <?php if ($grupo_id && $students_result->num_rows > 0): ?>
                <form action="registrar_asistencia.php" method="post">
                    <input type="hidden" name="grupo" value="<?php echo $grupo_id; ?>">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" required>

                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Asistió</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students_result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['Nombre']); ?></td>
                                    <td>
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="1" required> Sí
                                        <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="0"> No
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <input type="submit" name="register_attendance" value="Registrar Asistencia">
                </form>
            <?php else: ?>
                <p>Por favor, selecciona un grupo para registrar asistencia.</p>
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