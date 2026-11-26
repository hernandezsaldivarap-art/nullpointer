<?php
require_once "conexion.php";
$data = json_decode(file_get_contents("php://input"), true);
$accion = $data["accion"] ?? "";

if ($accion === "register") {
    $nombre = trim($data["name"] ?? "");
    $email  = strtolower(trim($data["email"] ?? ""));
    $pass   = password_hash($data["password"] ?? "", PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(["ok" => false, "error" => "Este correo ya está registrado."]);
        exit;
    }

    $ins = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $ins->execute([$nombre, $email, $pass]);

    echo json_encode([
        "ok" => true,
        "user" => [
            "id" => $pdo->lastInsertId(),
            "name" => $nombre,
            "email" => $email,
            "rol" => "cliente"
        ]
    ]);
}
elseif ($accion === "login") {
    $email = strtolower(trim($data["email"] ?? ""));
    $pass  = $data["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user["password"])) {
        echo json_encode(["ok" => false, "error" => "Correo o contraseña incorrectos."]);
        exit;
    }

    echo json_encode([
        "ok" => true,
        "user" => [
            "id" => $user["id_usuario"],
            "name" => $user["nombre"],
            "email" => $user["email"],
            "rol" => $user["rol"]
        ]
    ]);
}
elseif ($accion === "recover" || $accion === "change_pass") {
    $email = strtolower(trim($data["email"] ?? ""));
    $newPass = password_hash($data["newPassword"] ?? "", PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(["ok" => false, "error" => "No encontramos ninguna cuenta con ese correo."]);
        exit;
    }

    if ($accion === "change_pass" && !password_verify($data["oldPassword"] ?? "", $user["password"])) {
        echo json_encode(["ok" => false, "error" => "La contraseña actual es incorrecta."]);
        exit;
    }

    $upd = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
    $upd->execute([$newPass, $email]);

    echo json_encode(["ok" => true]);
}
?>