#!/bin/bash
# wix.sh - Agustin Diaz 7°1
# Uso: ./wix.sh DOMINIO   -> crea la carpeta DOMINIO con la estructura de un sitio web
dominio="$1"
if [ -z "$dominio" ]; then echo "Uso: ./wix.sh dominio"; exit 1; fi
cd "$(dirname "$0")" || exit 1
if [ -e "$dominio" ]; then echo "Ya existe $dominio"; exit 1; fi

mkdir -p "$dominio"/css/{user,admin} "$dominio"/img/{avatars,buttons,products,pets} \
         "$dominio"/js/{validations,effects} "$dominio"/tpl "$dominio"/php

cat > "$dominio/index.php" <<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$dominio</title>
<link rel="stylesheet" href="css/user/estilo.css">
</head>
<body>
<h1>$dominio</h1>
<p>Sitio creado con webgenerator Agustin Diaz.</p>
</body>
</html>
HTML
echo "body { font-family: Arial, sans-serif; text-align: center; margin-top: 60px; }" > "$dominio/css/user/estilo.css"
touch "$dominio"/css/admin/estilo.css \
      "$dominio"/js/validations/{login,register}.js "$dominio"/js/effects/panel.js \
      "$dominio"/tpl/{main,login,register,panel,profile,crud}.tpl \
      "$dominio"/php/{create,read,update,delete,dbconect}.php
echo "Sitio $dominio creado"
