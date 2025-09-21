<?php
header("Content-Type: application/json");

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fast_delivery";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Leer el método de solicitud HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['pedido_id'])) {
            $pedido_id = intval($_GET['pedido_id']);
            $sql = "SELECT * FROM pedidos WHERE ID_Pedido = $pedido_id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $pedido = $result->fetch_assoc();
                echo json_encode($pedido);
            } else {
                echo json_encode(["error" => "Pedido no encontrado"]);
            }
        } else {
            echo json_encode(["error" => "ID de pedido requerido"]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $id_cliente = $data['ID_Cliente'];
        $fecha_pedido = $data['Fecha_Pedido'];
        $id_producto = $data['ID_Producto'];

        $sql = "INSERT INTO pedidos (ID_Cliente, Fecha_Pedido, Estado_Pedido, ID_Producto) 
                VALUES ('$id_cliente', '$fecha_pedido', 'pendiente', '$id_producto')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => "Pedido registrado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al registrar el pedido: " . $conn->error]);
        }
        break;

    default:
        echo json_encode(["error" => "Método no permitido"]);
        break;
}

$conn->close();
?>
