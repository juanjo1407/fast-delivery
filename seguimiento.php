<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "seguimiento_pedidos");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$pedido = null;
$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['numero_guia'])) {
    $numero_guia = $conexion->real_escape_string($_POST['numero_guia']);

    $sql = "SELECT p.ID_Pedido, p.Fecha_Pedido, p.Estado_Pedido, p.Ubicacion, p.numero_guia,
                   c.Nombre, c.Apellido, c.Direccion 
            FROM pedidos p 
            JOIN clientes c ON p.ID_Cliente = c.ID_Cliente 
            WHERE p.numero_guia = '$numero_guia'";
    $resultado = $conexion->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $pedido = $resultado->fetch_assoc();

        // Consultar detalle productos
        $sql_detalle = "SELECT dp.Cantidad, dp.Precio_Unitario, pr.Nombre_Producto, pr.Descripcion
                        FROM detalle_pedidos dp
                        JOIN productos pr ON dp.ID_Producto = pr.ID_Producto
                        WHERE dp.ID_Pedido = '{$pedido['ID_Pedido']}'";
        $resultado_detalle = $conexion->query($sql_detalle);
        $pedido['detalle'] = $resultado_detalle;
    } else {
        $mensaje_error = "No se encontró ningún pedido con ese número de guía.";
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>ENVIA PRODUCTOS | FAST DELIVERY</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="img/icon.png" rel="icon">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <style>
        .map-container {
            margin-top: 15px;
            width: 100%;
            max-width: 600px;
            height: 400px;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <!-- Top Bar Start -->
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
                    <a class="text-white px-2" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="text-white px-2" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="text-white px-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a class="text-white px-2" href="#"><i class="fab fa-instagram"></i></a>
                    <a class="text-white pl-2" href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Bar End -->

    <!-- Navbar Start -->
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
    <!-- Navbar End -->

    <!-- Formulario para consultar número de guía -->
    <div class="container my-5">
        <h2>Consulta tu pedido por número de guía</h2>
        <form method="POST" action="seguimiento.php" class="mb-4">
            <div class="form-group">
                <label for="numero_guia">Número de guía:</label>
                <input type="text" id="numero_guia" name="numero_guia" class="form-control" required
                    value="<?php echo htmlspecialchars($_POST['numero_guia'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Consultar</button>
        </form>

        <?php if ($mensaje_error): ?>
            <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <?php if ($pedido): ?>
            <h4>Estado del Pedido</h4>
            <p><strong>Número de Guía:</strong> <?php echo htmlspecialchars($pedido['numero_guia']); ?></p>
            <p><strong>Fecha del Pedido:</strong> <?php echo htmlspecialchars($pedido['Fecha_Pedido']); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($pedido['Estado_Pedido']); ?></p>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['Nombre'] . " " . $pedido['Apellido']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['Direccion']); ?></p>

            <?php if (!empty($pedido['Ubicacion'])): 
                $ubicacion = $pedido['Ubicacion'];
                $ubicacion_encoded = urlencode($ubicacion);
                $mapa_link = "https://www.google.com/maps?q=$ubicacion_encoded";
            ?>
                <p><strong>Ubicación Actual:</strong> <?php echo htmlspecialchars($ubicacion); ?> → 
                    <a href="<?php echo $mapa_link; ?>" target="_blank">Ver en el mapa (Google Maps)</a></p>

                <div class="map-container">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" style="border:0" 
                        src="https://maps.google.com/maps?q=<?php echo $ubicacion_encoded; ?>&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                        allowfullscreen>
                    </iframe>
                </div>
            <?php endif; ?>

            <?php if ($pedido['detalle'] && $pedido['detalle']->num_rows > 0): ?>
                <h5>Detalle del Pedido</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $pedido['detalle']->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['Nombre_Producto']); ?></td>
                                <td><?php echo htmlspecialchars($fila['Descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($fila['Cantidad']); ?></td>
                                <td>$ <?php echo number_format($fila['Precio_Unitario'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay detalles de productos asociados a este pedido.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer Start -->
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
    <!-- Footer End -->

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <script src="js/main.js"></script>
</body>

</html>
