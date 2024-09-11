<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}
include 'db_connect.php';

// Manejo de formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add']) || isset($_POST['edit'])) {
        $name = $_POST['name'] ?? '';
        $group_id = $_POST['group_id'] ?? '';

        if (empty($name) || empty($group_id)) {
            echo "<p style='color: red;'>Debe completar todos los campos (Nombre y Grupo).</p>";
        } else {
            if (isset($_POST['add'])) {
                $sql = "INSERT INTO estudiantes (Nombre, grupo_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $name, $group_id);
                $stmt->execute();
                echo "<p style='color: green;'>Estudiante agregado exitosamente.</p>";
            } elseif (isset($_POST['edit'])) {
                $id = $_POST['id'] ?? '';
                if (empty($id)) {
                    echo "<p style='color: red;'>ID del estudiante no proporcionado.</p>";
                } else {
                    $sql = "UPDATE estudiantes SET Nombre = ?, grupo_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sii", $name, $group_id, $id);
                    $stmt->execute();
                    echo "<p style='color: green;'>Estudiante actualizado exitosamente.</p>";
                }
            }
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo "<p style='color: red;'>ID del estudiante no proporcionado.</p>";
        } else {
            $sql = "DELETE FROM estudiantes WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo "<p style='color: green;'>Estudiante eliminado exitosamente.</p>";
        }
    }
}

$students_result = $conn->query("SELECT * FROM estudiantes");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Estudiantes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo del Sistema">
        </div>
        <h1>Gestionar Estudiantes</h1>
        <a href="index.php" class="button">Inicio</a>
    </header>
    <main>
        <section>
            <h2>Agregar/Editar Estudiante</h2>
            <form action="gestionar_estudiantes.php" method="post">
                <input type="hidden" name="id" id="id">
                <label for="name">Nombre:</label>
                <input type="text" name="name" id="name" required>

                <label for="group_id">Grupo:</label>
                <select name="group_id" id="group_id" <?php echo isset($_POST['delete']) ? 'disabled' : 'required'; ?>>
                    <option value="">Seleccione un grupo</option>
                    <?php
                    $groups_result = $conn->query("SELECT * FROM grupos");
                    while ($row = $groups_result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['nombre_grupo'] . "</option>";
                    }
                    ?>
                </select>

                <input type="submit" name="add" value="Agregar Estudiante">
                <input type="submit" name="edit" value="Editar Estudiante">
            </form>
        </section>
        <section>
            <h2>Lista de Estudiantes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Grupo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo htmlspecialchars($student['Nombre']); ?></td>
                            <td><?php
                                $group_result = $conn->query("SELECT nombre_grupo FROM grupos WHERE id = " . $student['grupo_id']);
                                $group = $group_result->fetch_assoc();
                                
                                if ($group) {
                                    echo htmlspecialchars($group['nombre_grupo']);
                                } else {
                                    echo "<span style='color: red;'>Grupo no asignado</span>";
                                }
                                ?></td>
                            <td>
                                <form action="gestionar_estudiantes.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <input type="submit" name="delete" value="Eliminar">
                                </form>
                                <button onclick="editStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['Nombre']); ?>', <?php echo $student['grupo_id']; ?>)">Editar</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Sistema de Asistencia. Todos los derechos reservados.</p>
    </footer>

    <script>
    function editStudent(id, name, groupId) {
        document.getElementById('id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('group_id').value = groupId;
    }
    </script>
</body>
</html>