<?php

session_start();
unset($_SESSION['id_usuario']);
unset($_SESSION['email']);
unset($_SESSION['token']);
unset($_SESSION['fila']);
unset($_SESSION['recordatorio']);
unset($_SESSION['nivel']);
unset($_SESSION['nombre']);
session_destroy();

header('Location: login.php');

?>