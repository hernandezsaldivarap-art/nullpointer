<?php
header("Content-Type: application/json; charset=UTF-8");

$host = "sql208.infinityfree.com";
$db   = "if0_43526290_nullpointer_db";
$user = "if0_43526290";
$pass = "PEGA_AQUI_TU_CONTRASEÑA";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Error de conexión a MySQL: " . $e->getMessage()]);
    exit;
}
?>