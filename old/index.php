<?php
    if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
        header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
        exit;
    }

    include('config/config.php');
    include('config/db.php');
    include('config/func.php');
    
    session_start();
    
    $incluir = 1;
    
    $comprobacion_token = "SELECT fecha_max FROM token WHERE id_usuario = '". $_SESSION['id_usuario'] ."' AND token = '". $_SESSION['token'] ."' ";
    conectar();
    if($respuesta = mysqli_query($db, $comprobacion_token)) {
        $row = mysqli_fetch_assoc($respuesta);
        $fecha_ahora = strtotime(date('c'));
        $fecha_max = strtotime($row['fecha_max']);
        $intervalo = $fecha_max - $fecha_ahora;
        
        if($intervalo < 0 || session_status() != 2) header('Location: cerrar_sesion.php'); 
    }
    mysqli_free_result($respuesta);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>ControlAppQR</title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">                   <!-- SIRVE PARA INTERNET EXPLORER -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- FORMA EN QUE SE VE LA VISTA EN MOVIL-->

        <!-- BOOTSTRAP CSS -->
        <link href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" rel="stylesheet" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" >
        <link href="css/jquery-ui.min.css" rel="stylesheet">
        <link href="css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="css/ControlAppQR.css" rel="stylesheet">
    </head>
    <body>
        <?php include('cabecera.php'); ?>
        <div class="mt-5 pt-3" id="cuerpo" style="position:relative">
            <?php
                if(isset($_GET['p'])) {
                    switch($_GET['p']) {
                        case 'registros':           include 'registros.php';            break;
                        case 'escanear':            include 'escanear.php';             break;
                        case 'eventos':             include 'eventos.php';              break;
                        case 'reg_evento':          include 'reg_evento.php';           break;
                        case 'usuarios':            include 'usuarios.php';             break;
                        case 'reg_usuario':         include 'reg_usuario.php';          break;
                        case 'login':               include 'login.php';                break;
                        case 'cerrar_sesion':       include 'cerrar_sesion.php';        break;
                        default:                    include 'escanear.php';
                    }
                } else {
                    include 'login.php';
                }
            ?>
        </div>
        
        <!-- SCRIPTS -->
        <script src="js/jquery-3.4.1.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/jquery-ui.min.js"></script>
        
        <script src="js/ControlAppQR.php"></script>
        <?php 
            if(isset($_GET['p'])) {
                switch($_GET['p']) {
                    case 'registros':
                        echo '<script src="js/jquery.dataTables.min.js"></script>';
                        echo '<script src="js/dataTables.bootstrap4.min.js"></script>';
                        echo '<script src="js/registros.php"></script>';
                    break;
                    case 'escanear':  
                        echo '<script src="js/sweetalert2@11.min.js"></script>';   
                        echo '<script src="js/escanear.php"></script>';
                    break;
                    case 'eventos': 
                        echo '<script src="js/jquery.dataTables.min.js"></script>'; 
                        echo '<script src="js/dataTables.bootstrap4.min.js"></script>';     
                        echo '<script src="js/eventos.php"></script>';
                    break;
                    case 'reg_evento':   
                        echo '<script src="js/sweetalert2@11.min.js"></script>';    
                        echo '<script src="js/reg_evento.php"></script>';
                    break;
                    case 'usuarios':   
                        echo '<script src="js/jquery.dataTables.min.js"></script>';  
                        echo '<script src="js/dataTables.bootstrap4.min.js"></script>';  
                        echo '<script src="js/usuarios.php"></script>';
                    break;
                    case 'reg_usuario':       
                        echo '<script src="js/sweetalert2@11.min.js"></script>';
                        echo '<script src="js/reg_usuario.php"></script>';
                    break;
                }   
            } else {
                echo '<script src="js/jquery.dataTables.min.js"></script>';
                echo '<script src="js/dataTables.bootstrap4.min.js"></script>';
                echo '<script src="js/registros.php"></script>';
            }
        ?>
    </body> 
</html>
<?php 
    mysqli_close($db);
?>
