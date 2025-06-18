<?php
include('config/db.php');
include('config/func.php');

session_start();

if(isset($_POST['email']) && isset($_POST['pass'])) {
    $email = $_POST['email'];
    $password = $_POST['pass'];

    $user   = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
	$domain = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+';
	$ipv4   = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
    $ipv6   = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';

    if(preg_match("/^$user@($domain|(\[($ipv4|$ipv6)\]))$/", $email)) {
        
        $consulta = "SELECT id, nombre, apellidos, pass, nivel, id_fila FROM usuarios WHERE email LIKE '". $email . "' ";

        if($resultado = mysqli_query($db, $consulta)) {

            $row = mysqli_fetch_assoc($resultado);
            if($row['nivel'] < 10) {
                if($row["id"]) {         
                    $hash = $row["pass"];
                    if(verificar_pass($password, $hash)) {
                        $hora = date('c');
                        $session_id = session_id();
                        $fecha = strtotime($hora."+ 3 days");
                        $fecha_max = date("Y-m-d H:i:s", $fecha); 
                        $token = crear_pass($fecha_max.$session_id);

                        $_SESSION['id_usuario'] = $row['id'];
                        $_SESSION['email'] = md5($email);
                        $_SESSION['token'] = $token;
                        $_SESSION['fila'] = $row['id_fila'];
                        $_SESSION['nivel'] = $row['nivel'];
                        $_SESSION['nombre'] = $row['nombre'] .' '. $row['apellidos'];

                        $sql_token = "INSERT INTO token (id_usuario, fecha_max, token)
                            VALUES ('". $_SESSION['id_usuario'] ."','". $fecha_max ."','". $_SESSION['token'] ."') ";
                        if($resultado = mysqli_query($db, $sql_token)) {
                            echo 'Redireccionando.';

                            header("Location: index.php?p=escanear", true, 301);
                        } else {
                            echo 'No se ha podido crear la sesión.';
                        }
                        
                    } else {
                        echo 'Email o contraseña incorrecto.';
                    }
                } else {
                    echo 'No se ha podido asignar ningún id';
                }  
            } else {
                echo 'No tienes acceso.';
            }
        } else {
            echo 'No se han podido verificar los datos.';
        }
        mysqli_free_result($resultado);
    } else {
        echo 'Datos con formato incorrecto.';
    }
    mysqli_close($db);
} else {
    echo 'Datos no recibidos.';
}

?>

