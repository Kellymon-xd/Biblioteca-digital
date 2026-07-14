<?php
// Redirección de compatibilidad para XAMPP si el DocumentRoot apunta a la raíz del proyecto.
// En producción el DocumentRoot correcto debe ser /public.
$path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$query = $_SERVER['QUERY_STRING'] ?? '';
$target = ($path === '' ? '' : $path) . '/public/index.php' . ($query !== '' ? '?' . $query : '');
header('Location: ' . $target, true, 302);
exit;
