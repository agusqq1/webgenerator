<?php
session_start();
require "conexion.php";
if (isset($_SESSION["idUsuario"])) { header("Location: panel.php"); exit; }

$error = ""; $ok = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass  = $_POST["password"] ?? "";
    $pass2 = $_POST["password2"] ?? "";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido.";
    } elseif ($pass === "" || $pass !== $pass2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $st = $conexion->prepare("SELECT 1 FROM usuarios WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetch()) {
            $error = "Ese email ya está registrado.";
        } else {
            $st = $conexion->prepare("INSERT INTO usuarios (email, password, fechaRegistro) VALUES (?, ?, NOW())");
            $st->execute([$email, password_hash($pass, PASSWORD_DEFAULT)]);
            $ok = true;
            header("Refresh: 2; url=login.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Registrarte es simple.</title><link rel="stylesheet" href="estilo.css"></head>
<body><div class="caja">
<h1>Registrarte es simple.</h1>
<?php if ($ok): ?>
  <p class="ok">¡Registro exitoso! Te llevamos al login...</p>
  <p><a href="login.php">Ir al login</a></p>
<?php else: ?>
  <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
  <form method="post">
    <label>Email <input type="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"></label>
    <label>Contraseña <input type="password" name="password" required></label>
    <label>Repetir contraseña <input type="password" name="password2" required></label>
    <button type="submit">Registrarme</button>
  </form>
  <p><a href="login.php">Ya tengo cuenta</a></p>
<?php endif; ?>
</div></body></html>
