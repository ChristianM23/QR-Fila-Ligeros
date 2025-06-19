<?php

function conectar() {
    //$db = new mysqli("qaix219.pipopu.es", "qaix219", "CHpipopu.23", "qaix219");
    $db = new mysqli("localhost", "root", "", "filaqr");
    mysqli_set_charset($db, 'utf8');
}

function crear_pass($pass) {
    $opciones = [
        'cost' => 12,
    ];
    return password_hash($pass, PASSWORD_BCRYPT, $opciones);
}

function verificar_pass($pass,$hash) {
    if (password_verify($pass,$hash)) {
        return true;
    } else {
        return false;
    }
}

function imageToBase64($path) {
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path, false, stream_context_create($arrContextOptions));
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    return $base64;
}

?>