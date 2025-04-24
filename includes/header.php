<?php
// Este archivo debe ser incluido después de iniciar la sesión
// Asegúrate de que session_start() ya se ha llamado en el archivo principal

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit;
}

// Obtener el tipo de usuario desde la sesión
$tipo_usuario = $_SESSION['usuario_tipo'] ?? 0;

// Determinar la página actual
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<header class="header">
  <nav class="dashboard-header container">
    <h1 class="title"><?php echo $titulo_pagina ?? 'Dashboard'; ?></h1>
    <?php if ($pagina_actual == 'dashboard.php' || $pagina_actual == "usuarios.php"): ?>
      <ul class="navbar-nav">
        <li class="nav-item"><a href="dashboard.php" <?php echo ($pagina_actual == 'dashboard.php') ? 'class="active"' : ''; ?>>Inicio</a></li>

        <?php if ($tipo_usuario == 1): ?>
          <!-- Solo mostrar el enlace a usuarios si el usuario es de tipo 1 (administrador) -->
          <li class="nav-item"><a href="usuarios.php" <?php echo ($pagina_actual == 'usuarios.php') ? 'class="active"' : ''; ?>>Usuarios</a></li>
        <?php endif; ?>

        <li class="nav-item"><a href="calendario.php" target="_blank" <?php echo ($pagina_actual == 'calendario.php') ? 'class="active"' : ''; ?>>Calendario</a></li>
      </ul>

      <!-- Mostrar el botón de salir solo si NO estamos en la página calendario.php -->
      <button class="btn btn-danger" onclick="confirmLogout()">Salir</button>
    <?php endif; ?>
  </nav>
</header>

<?php if ($pagina_actual != 'calendario.php'): ?>
  <script>
    // Función para confirmar el cierre de sesión
    function confirmLogout() {
      if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
        window.location.href = "logout.php"; // Redirigir a la página de cierre de sesión
      }
    }
  </script>
<?php endif; ?>