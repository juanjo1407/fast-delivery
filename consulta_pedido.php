<?php
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

$output = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_guia = trim($_POST['numero_guia']); // Eliminar espacios en blanco

    // Validar el número de guía
    if (!empty($numero_guia) && preg_match("/^[A-Za-z0-9]+$/", $numero_guia)) {
        // Preparar consulta
        $stmt = $conn->prepare("
            SELECT 
                pedidos.numero_guia,
                pedidos.Estado_Pedido, 
                clientes.Nombre AS nombre_cliente, 
                clientes.Apellido AS apellido_cliente, 
                productos.Nombre_Producto 
            FROM 
                pedidos 
                JOIN clientes ON pedidos.ID_Cliente = clientes.ID_Cliente 
                JOIN detalle_pedidos ON pedidos.ID_Pedido = detalle_pedidos.ID_Pedido 
                JOIN productos ON detalle_pedidos.ID_Producto = productos.ID_Producto 
            WHERE 
                pedidos.numero_guia = ?
        ");
        $stmt->bind_param("s", $numero_guia);
        
        // Ejecutar consulta
        if (!$stmt->execute()) {
            die("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Obtener resultados
            $row = $result->fetch_assoc();
            $numero_guia = $row['numero_guia'];
            $estado_existente = $row['Estado_Pedido'];
            $nombre_cliente = $row['nombre_cliente'] . " " . $row['apellido_cliente'];
            $nombre_producto = $row['Nombre_Producto'];

            $output = "
                <div class='card mt-4 shadow-lg'>
                    <div class='card-header bg-primary text-white'>
                        <h5 class='card-title mb-0'>Información del Pedido</h5>
                    </div>
                    <div class='card-body'>
                        <ul class='list-group'>
                            <li class='list-group-item'>
                                <strong>Número de Guía:</strong> $numero_guia
                            </li>
                            <li class='list-group-item'>
                                <strong>Cliente:</strong> $nombre_cliente
                            </li>
                            <li class='list-group-item'>
                                <strong>Estado del Pedido:</strong> <span class='badge badge-info'>$estado_existente</span>
                            </li>
                            <li class='list-group-item'>
                                <strong>Producto:</strong> $nombre_producto
                            </li>
                        </ul>
                    </div>
                    <div class='card-footer text-muted'>
                        Gracias por preferirnos, ¡FAST DELIVERY!
                    </div>
                </div>
            ";
        } else {
            $output = "<div class='alert alert-danger mt-3'>No se encontró ningún pedido con el número de guía ingresado.</div>";
        }
        
        $stmt->close();
    } else {
        $output = "<div class='alert alert-warning mt-3'>Por favor ingresa un número de guía válido.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
    <title>ENVIA PRODUCTOS | FAST DELIVERY</title>
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
                    <a href="seguimiento.html" class="nav-item nav-link">SEGUIMIENTO</a>  <!-- NUEVO -->
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
        <h2>Consultar Pedido</h2>
        <form method="POST" class="mb-4">
            <div class="form-group">
                <label for="numero_guia">Número de Guía:</label>
                <input type="text" class="form-control" name="numero_guia" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Consultar</button>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
