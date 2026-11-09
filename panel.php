<?php
session_start();
require "conexion.php";
if (!isset($_SESSION["idUsuario"])) { header("Location: login.php"); exit; }

$id = (int)$_SESSION["idUsuario"];
$esAdmin = ($_SESSION["email"] === "admin@server.com");
$dir = __DIR__;
$error = ""; $msg = "";

// Busca una web y verifica que sea del usuario (el admin puede todas)
function buscarWeb($conexion, $idWeb, $id, $esAdmin) {
    $st = $conexion->prepare("SELECT * FROM webs WHERE idWeb = ?");
    $st->execute([$idWeb]);
    $w = $st->fetch(PDO::FETCH_ASSOC);
    if (!$w || (!$esAdmin && (int)$w["idUsuario"] !== $id)) return null;
    return $w;
}

// Crear web
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $nombre)) {
        $error = "El nombre solo puede tener letras, números, guiones y guiones bajos (sin espacios).";
    } else {
        $dominio = $id . $nombre;
        $st = $conexion->prepare("SELECT 1 FROM webs WHERE dominio = ?");
        $st->execute([$dominio]);
        if ($st->fetch()) {
            $error = "El dominio $dominio ya existe.";
        } else {
            $st = $conexion->prepare("INSERT INTO webs (idUsuario, dominio, fechaCreacion) VALUES (?, ?, NOW())");
            $st->execute([$id, $dominio]);
            shell_exec("bash " . escapeshellarg("$dir/wix.sh") . " " . escapeshellarg($dominio) . " 2>&1");
            header("Location: panel.php?creada=" . urlencode($dominio)); exit;
        }
    }
}

// Descargar web: comprime la carpeta en un zip y redirecciona a la descarga
if (isset($_GET["descargar"])) {
    $w = buscarWeb($conexion, (int)$_GET["descargar"], $id, $esAdmin);
    if ($w && is_dir("$dir/" . $w["dominio"])) {
        $d = $w["dominio"];
        @mkdir("$dir/descargas");
        @unlink("$dir/descargas/$d.zip");
        shell_exec("cd " . escapeshellarg($dir) . " && zip -rq " . escapeshellarg("descargas/$d.zip") . " " . escapeshellarg($d));
        header("Location: descargas/" . rawurlencode($d) . ".zip"); exit;
    }
    $error = "No se pudo descargar esa web.";
}

// Eliminar web: borra la carpeta y el registro
if (isset($_GET["eliminar"])) {
    $w = buscarWeb($conexion, (int)$_GET["eliminar"], $id, $esAdmin);
    if ($w) {
        $d = $w["dominio"];
        shell_exec("rm -rf " . escapeshellarg("$dir/$d") . " " . escapeshellarg("$dir/descargas/$d.zip"));
        $conexion->prepare("DELETE FROM webs WHERE idWeb = ?")->execute([$w["idWeb"]]);
        header("Location: panel.php?eliminada=" . urlencode($d)); exit;
    }
    $error = "No se pudo eliminar esa web.";
}

if (isset($_GET["creada"]))    $msg = "Web " . htmlspecialchars($_GET["creada"]) . " creada.";
if (isset($_GET["eliminada"])) $msg = "Web " . htmlspecialchars($_GET["eliminada"]) . " eliminada.";

// Listado: el admin ve todas las webs de todos los usuarios
if ($esAdmin) {
    $webs = $conexion->query("SELECT w.*, u.email FROM webs w JOIN usuarios u ON u.idUsuario = w.idUsuario ORDER BY w.idWeb")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $st = $conexion->prepare("SELECT * FROM webs WHERE idUsuario = ? ORDER BY idWeb");
    $st->execute([$id]);
    $webs = $st->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Bienvenido a tu panel</title><link rel="stylesheet" href="estilo.css"></head>
<body><div class="caja">
<h1>Bienvenido a tu panel</h1>
<p><a href="logout.php">Cerrar sesión de <?= $id ?></a></p>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($msg): ?><p class="ok"><?= $msg ?></p><?php endif; ?>

<form method="post">
  <label>Generar Web de: <input type="text" name="nombre" required placeholder="nombre de tu web"></label>
  <button type="submit">Crear web</button>
</form>

<h3><?= $esAdmin ? "Todas las webs (modo admin)" : "Tus webs" ?></h3>
<?php if (!$webs): ?>
  <p>Todavía no hay webs.</p>
<?php else: ?>
  <ul>
  <?php foreach ($webs as $w): $d = htmlspecialchars($w["dominio"]); ?>
    <li>
      <a href="<?= rawurlencode($w["dominio"]) ?>/index.php" target="_blank"><?= $d ?></a>
      <?php if ($esAdmin): ?><small>(<?= htmlspecialchars($w["email"]) ?>)</small><?php endif; ?>
      <span class="acciones">
        <a href="panel.php?descargar=<?= $w["idWeb"] ?>">descargar web</a>
        <a href="panel.php?eliminar=<?= $w["idWeb"] ?>" onclick="return confirm('¿Eliminar <?= $d ?>?')">Eliminar</a>
      </span>
    </li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>
</div></body></html>
