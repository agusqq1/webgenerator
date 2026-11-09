<?php
session_start();
require "conexion.php";
if (isset($_SESSION["idUsuario"])) { header("Location: panel.php"); exit; }

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass  = $_POST["password"] ?? "";
    $st = $conexion->prepare("SELECT idUsuario, email, password FROM usuarios WHERE email = ?");
    $st->execute([$email]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    if ($u && password_verify($pass, $u["password"])) {
        $_SESSION["idUsuario"] = $u["idUsuario"];
        $_SESSION["email"] = $u["email"];
        header("Location: panel.php"); exit;
    }
    $error = "Email o contraseña incorrectos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>webgenerator Agustin Diaz</title><link rel="stylesheet" href="estilo.css"></head>
<body><div class="caja">
<h1>webgenerator Agustin Diaz</h1>
<?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
<form method="post">
  <label>Email <input type="email" name="email" required></label>
  <label>Contraseña <input type="password" name="password" required></label>
  <p><a href="register.php">¿No tenés cuenta? Registrate</a></p>
  <button type="submit">Ingresar</button>
</form>
</div></body></html>
