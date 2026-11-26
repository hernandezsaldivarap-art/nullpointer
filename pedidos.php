<?php
require_once "conexion.php";
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $folio = strtoupper(trim($_GET["folio"] ?? ""));
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE folio = ?");
    $stmt->execute([$folio]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        echo json_encode(["ok" => false, "error" => "Folio no encontrado"]);
        exit;
    }

    $det = $pdo->prepare("SELECT * FROM detalle_pedidos WHERE id_pedido = ?");
    $det->execute([$pedido["id_pedido"]]);
    $pedido["items"] = $det->fetchAll();

    echo json_encode(["ok" => true, "order" => $pedido]);
}

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO pedidos (
            folio, id_usuario, fecha_pedido, entrega_estimada, total,
            metodo_pago, info_pago, referencia_oxxo, destinatario,
            telefono_completo, pais, codigo_postal, estado, ciudad,
            colonia, calle, numero_ext_int, referencias
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data["id"],
            $data["userId"],
            $data["date"],
            $data["estimatedDelivery"],
            $data["total"],
            $data["paymentMethod"],
            $data["paymentInfo"],
            $data["oxxoReference"] ?? null,
            $data["shipping"]["recipient"],
            $data["shipping"]["fullPhone"],
            $data["shipping"]["country"],
            $data["shipping"]["zip"],
            $data["shipping"]["state"],
            $data["shipping"]["city"],
            $data["shipping"]["colony"],
            $data["shipping"]["street"],
            $data["shipping"]["number"],
            $data["shipping"]["refs"]
        ]);

        $idPedidoDB = $pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO detalle_pedidos (
            id_pedido, id_producto, nombre_producto, color, talla, cantidad, precio_unitario
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($data["items"] as $item) {
            $stmtItem->execute([
                $idPedidoDB,
                $item["id"],
                $item["name"],
                $item["color"],
                $item["size"] ?? null,
                $item["qty"],
                $item["price"]
            ]);
        }

        $pdo->commit();
        echo json_encode(["ok" => true, "id_pedido" => $idPedidoDB]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["ok" => false, "error" => $e->getMessage()]);
    }
}
?>