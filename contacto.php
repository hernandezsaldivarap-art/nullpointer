<?php
require_once "conexion.php";
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("INSERT INTO mensajes_contacto (ticket_soporte, nombre, correo, telefono, motivo, mensaje) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $data["ticket"],
    $data["nombre"],
    $data["correo"],
    $data["telefono"],
    $data["motivo"],
    $data["mensaje"]
]);

echo json_encode(["ok" => true]);
?>