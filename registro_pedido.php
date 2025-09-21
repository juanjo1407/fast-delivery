<?php
// Incluir Twilio
require_once 'C:/xampp/htdocs/logistic/vendor/autoload.php'; // Ruta correcta al archivo autoload.php

use Twilio\Rest\Client;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seguimiento_pedidos";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Variable para mostrar mensajes
$output = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_guia = trim($_POST['numero_guia']);
    $cliente_id = trim($_POST['cliente']);
    $producto_id = trim($_POST['producto']);
    $estado = trim($_POST['estado']);

    // Validar los datos
    if (!empty($numero_guia) && preg_match("/^[A-Za-z0-9]+$/", $numero_guia) && 
        !empty($cliente_id) && !empty($producto_id) && !empty($estado)) {

        // Verificar si el cliente existe
        $stmtCliente = $conn->prepare("SELECT * FROM clientes WHERE ID_Cliente = ?");
        $stmtCliente->bind_param("i", $cliente_id);
        $stmtCliente->execute();
        $resultCliente = $stmtCliente->get_result();

        if ($resultCliente->num_rows > 0) {
            // Preparar consulta para insertar el pedido
            $stmt = $conn->prepare("INSERT INTO pedidos (numero_guia, ID_Cliente, Estado_Pedido) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $numero_guia, $cliente_id, $estado);
            
            // Ejecutar la consulta de inserción
            if ($stmt->execute()) {
                $id_pedido = $stmt->insert_id;

                // Preparar consulta para insertar detalles del pedido
                $stmtDetalle = $conn->prepare("INSERT INTO detalle_pedidos (ID_Pedido, ID_Producto, Cantidad, Precio_Unitario) VALUES (?, ?, ?, ?)");
                $cantidad = 1;

                // Obtener el precio del producto
                $stmtPrecio = $conn->prepare("SELECT Precio FROM productos WHERE ID_Producto = ?");
                $stmtPrecio->bind_param("i", $producto_id);
                $stmtPrecio->execute();
                $stmtPrecio->bind_result($precio_unitario);
                $stmtPrecio->fetch();
                $stmtPrecio->close();

                $stmtDetalle->bind_param("iiid", $id_pedido, $producto_id, $cantidad, $precio_unitario);
                
                // Ejecutar la consulta de detalles del pedido
                if ($stmtDetalle->execute()) {
                    $output = "<p class='text-success'>Pedido registrado exitosamente.</p>";

                    // Enviar notificación por SMS usando Twilio
                    $sid    = "AC07ccfed24973541417d74c71c0bd35e2";
                    $token  = "10e3b01db816fe63b56d0482191ccf5f";
                    $twilio_number = '+12316558076';
                    
                    $client = new Client($sid, $token);
                    
                    $cliente_phone = "+573223781097"; // Número del cliente
                    $numero_guia = $_POST['numero_guia']; // Obtiene el número de guía
                    $estado = $_POST['estado']; // Obtiene el estado del pedido

                    // Obtener el número de teléfono del cliente
                    $rowCliente = $resultCliente->fetch_assoc();
                    $cliente_phone = $rowCliente['Telefono']; // Asegúrate de que 'Telefono' sea el nombre de la columna en tu tabla

                  

                    try {
                       
                        $message = $client->messages->create(
                            $cliente_phone, // Número al que se enviará el SMS
                            array(
                                'from' => $twilio_number,
                                'body' => "Se ha registrado tu pedido. Número de guía: $numero_guia. Estado: $estado."
                            )
                        );                        

                        $output .= "<p class='text-success'>SMS enviado correctamente al cliente.</p>";
                    } catch (Exception $e) {
                        $output .= "<p class='text-warning'>El pedido se registró, pero no se pudo enviar el SMS: {$e->getMessage()}</p>";
                    }
                    
                } else {
                    $output = "<p class='text-danger'>Error al registrar los detalles del pedido: " . $stmtDetalle->error . "</p>";
                }
                $stmtDetalle->close();
            } else {
                $output = "<p class='text-danger'>Error al registrar el pedido: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            $output = "<p class='text-danger'>El ID de cliente no existe. Por favor verifica e intenta nuevamente.</p>";
        }
        $stmtCliente->close();
    } else {
        $output = "<p class='text-danger'>Por favor completa todos los campos correctamente.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <title>REGISTRA TU GUIA | FAST DELIVERY</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">
    <link href="img/icon.png" rel="icon">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid bg-dark">
        <div class="row py-2 px-lg-5">
            <div class="col-lg-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center text-white">
                    <small><i class="fa fa-phone-alt mr-2"></i>(+57) 322 3781097</small>
                    <small class="px-3">|</small>
                    <small><i class="fa fa-envelope mr-2"></i>fastdelivery@hotmail.com</small>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-white px-2" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="text-white px-2" href=""><i class="fab fa-twitter"></i></a>
                    <a class="text-white px-2" href=""><i class="fab fa-linkedin-in"></i></a>
                    <a class="text-white px-2" href=""><i class="fab fa-instagram"></i></a>
                    <a class="text-white pl-2" href=""><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid p-0">
        <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-lg-5">
            <a href="index.html" class="navbar-brand ml-lg-3">
                <h1 class="m-0 display-5 text-uppercase text-primary"><i class="fa fa-truck mr-2"></i>Fast Delivery</h1>
            </a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between px-lg-3" id="navbarCollapse">
                <div class="navbar-nav m-auto py-0">
                    <a href="index.html" class="nav-item nav-link active">INICIO</a>
                    <a href="about.html" class="nav-item nav-link">NOSOTROS</a>
                    <a href="service.html" class="nav-item nav-link">SERVICIOS</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">ÚNETE</a>
                        <div class="dropdown-menu rounded-0 m-0">
                            <a href="single.html" class="dropdown-item">Trabaja con Nosotros</a>
                        </div>
                    </div>
                    <a href="contact.html" class="nav-item nav-link">REGISTRA TU GUÍA</a>
                </div>
            </div>
        </nav>
    </div>

    <div class="container mt-5">
        <h2>Registrar Pedido</h2>
        <form method="POST">
            <div class="form-group">
                <label for="numero_guia">Número de Guía:</label>
                <input type="text" class="form-control" name="numero_guia" required>
            </div>
            <div class="form-group">
                <label for="cliente">Cliente ID:</label>
                <input type="text" class="form-control" name="cliente" required>
            </div>
            <div class="form-group">
                <label for="producto">Producto ID:</label>
                <input type="text" class="form-control" name="producto" required>
            </div>
            <div class="form-group">
                <label for="estado">Estado del Pedido:</label>
                <input type="text" class="form-control" name="estado" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Pedido</button>
        </form>
        <?= $output; ?>
    </div>
<div class="container-fluid bg-dark text-white mt-5 py-5 px-sm-3 px-md-5">
        <div class="row pt-5">
            <div class="col-lg-7 col-md-6">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <h3 class="text-primary mb-4">Contacto</h3>
                        <p><i class="fa fa-map-marker-alt mr-2"></i>Girardot Cundinamarca</p>
                        <p><i class="fa fa-phone-alt mr-2"></i>(+57) 322 3781097</p>
                        <p><i class="fa fa-envelope mr-2"></i>fastdelivery@hotmail.com</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-outline-light btn-social" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h3 class="text-primary mb-4">Acceso Rápido</h3>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-white mb-2" href="index.html"><i class="fa fa-angle-right mr-2"></i>Inicio</a>
                            <a class="text-white mb-2" href="about.html"><i class="fa fa-angle-right mr-2"></i>Nosotros</a>
                            <a class="text-white mb-2" href="service.html"><i class="fa fa-angle-right mr-2"></i>Otros Servicios</a>
                            <a class="text-white" href="contact.html"><i class="fa fa-angle-right mr-2"></i>Registra tu guía</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-6 mb-5">
                <h3 class="text-primary mb-4">Boletín Informativo</h3>
                <p>Esta empresa se encuentra Vigilada y Controlada por MINTIC, INDUSTRIA Y COMERCIO
                    Y VIGILADO SUPER TRANSPORTE
                </p>
                <div class="w-100">
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: #3E3E4E !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white">&copy; <a href="#">Juan</a>. Todos los derechos reservados. 
				
            </div>
            <div class="col-lg-6 text-center text-md-right">
                <ul class="nav d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Privacidad</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Terminos y Condiciones</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <a href="#" class="btn btn-lg btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>
    
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="mail/jqBootstrapValidation.min.js"></script>
    <script src="mail/contact.js"></script>
    <script src="js/main.js"></script>
    
</body>
</html>



