<?php 
    header("Content-type: application/javascript; charset=utf-8", true); 
    session_start();
    include('../config/config.php');
?>
$(document).ready(function() {
    if($(location).attr('href').includes("u=")) {
        $("button").html("Actualizar");
        $(".titulo").html("Actualizar usuario");
    }

    if($(location).attr('href').includes("u=")) {
        $.ajax({
            url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&c=info_usuario",
            type: 'post',
            data: {id_usuario:$(location).attr('href').split("u=")[1]}
        }).done(function(usuario) {
            var obj = JSON.parse(usuario);
            
            $("#nombre").val(obj[0].Nombre);
            $("#apellidos").val(obj[0].Apellidos);
            $("#num_fila").val(obj[0].Num_fila);
            $("#figura").val(obj[0].Figura);
            $("#cargo").val(obj[0].Nivel);
            $("#email").val(obj[0].Email);
            $("#telefono").val(obj[0].Telefono);
        });
    }

    $("button").on("click", function() {
        var url = "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&i=usuario";
        var id_usuario = "0";
        if($(location).attr('href').includes("u=")) {
            url = "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&u=usuario";
            id_usuario = $(location).attr('href').split("u=")[1];
        }

        $.ajax({
            url: url,
            type: 'post',
            data: {id_usuario:id_usuario, nombre:$("#nombre").val(), apellidos:$("#apellidos").val(), num_fila:$("#num_fila").val(),
                figura:$("#figura").val(), nivel:$("#cargo").val(), email:$("#email").val(), telefono:$("#telefono").val()}
        }).done(function(usuario) {
            if(usuario == 1) {
                var mensaje = "Usuario registrado.";
                if($(location).attr('href').includes("u=")) var mensaje = "Datos actualizados.";

                Swal.fire({
                    title: mensaje,
                    icon: 'success',
                    confirmButtonText: 'Vale'
                }).then((result) => {
                    if(result.isConfirmed) {
                        window.close();
                    }
                });
            } else {
                if(registro == 'Error 1') var mensaje = 'Error 1.';
                else if(registro == 'Error 2') var mensaje = 'Error 2.';
                else if(registro == 'Error 3') var mensaje = 'Error 3.';
                else if(registro == 'Error 4') var mensaje = 'Error 4.';
                else if(registro == 'Error 5') var mensaje = 'Error 5.';
                else if(registro == 'Error 6') var mensaje = 'Error 6.';
                else if(registro == 'Error 7') var mensaje = 'No se han trasmitido datos.';
                else if(registro == 'Error 8') var mensaje = 'No se ha podido realizar el registro.';
                else var mensaje = 'Algo ha fallado.';
                
                Swal.fire({
                    title: mensaje,
                    icon: 'error',
                    confirmButtonText: 'Vale'
                });
            }
        });
        
    });
});