<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo del Sistema">
        </div>
        <h1>Bienvenido al Sistema de Asistencia</h1>
    </header>
    <main>
        <section>
            <h2>Acciones Disponibles</h2>
            <p>Utiliza el menú de navegación para acceder a las diferentes secciones del sistema.</p>
            <nav>
            <ul>
                <li><a href="registrar_asistencia.php">Registrar Asistencia</a></li>
                <li><a href="ver_reportes.php">Ver Reportes</a></li>
                <li><a href="gestionar_estudiantes.php">Gestionar Estudiantes</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
            </nav>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Sistema de Asistencia. Todos los derechos reservados.</p>
    </footer>
</body>
</html>