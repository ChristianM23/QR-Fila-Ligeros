<?php 
    header("Content-type: application/javascript; charset=utf-8", true); 
    session_start();
    include('../config/config.php');
?>
$(document).ready(function() {

    focusEscribir();

    var datos = [];

    $("#btn_guardar").on("click", function() {
        var usuario = $("#qr").val().split("-")[1];
        var fila = $("#qr").val().split("-")[0];
        var d = new Date();
        var month = d.getMonth();
        var day = d.getDate();
        var min = d.getMinutes();
        var fecha = d.getFullYear() +'-'+ (month < 10 ? '0' : '') + month +'-'+ (day < 10 ? '0' : '') + day +' '+ d.getHours() +':'+ (min < 10 ? '0' : '') + min;
        datos.push({id_usuario:usuario, id_fila:fila, fecha_reg:fecha});

        $.ajax({
            url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&c=scan_usuario",
            type: 'post',
            data: {id_usuario:usuario, id_fila:fila}
        }).done(function(usuario) {
            var obj = JSON.parse(usuario);

            $("table tbody").append("<tr><td>"+ obj[0]['Id'] +"</td><td>"+ obj[0]['Nombre'] +"</td><td>"+ obj[0]['Figura'] +"</td><td>"+ fecha +"</td><td><i class='bi bi-trash3'></i></td></tr>");
            $("#qr").val("");
        });
    });

    $("#btn_enviar").on("click", function() {
        $.ajax({
            url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&i=escanear",
            type: 'post',
            data: {datos:datos}
        }).done(function(registro) {
            if(registro == 'Error 7' || registro == 'Error 8' || registro == 'Error 9') {
                if(registro == 'Error 7') var mensaje = 'No se han trasmitido datos.';
                else if(registro == 'Error 8') var mensaje = 'No se ha podido comprobar si hay eventos activos.';
                else if(registro == 'Error 9') var mensaje = 'No hay ningún evento activo.';
                else var mensaje = 'Algo ha fallado.';

                Swal.fire({
                    title: mensaje,
                    icon: 'error',
                    confirmButtonText: 'Vale'
                });
            } else {
                Swal.fire({
                    title: 'Datos registrados',
                    icon: 'success',
                    confirmButtonText: 'Vale'
                }).then((result) => {
                    if(result.isConfirmed) {
                        $(location).attr("href","index.php?p=escanear");
                    }
                });
            }
        });
    });

    $("#imgCodigoQR").on("click", function() {
        focusCodigoQR();
    });

    $("#imgEscribir").on("click", function() {
        focusEscribir();
    });

    function focusCodigoQR() {
        $("#qr").val('');
        $("#qr").focus();
        $("#qr").attr('readonly', 'readonly');
    }

    function focusEscribir() {
        $("#qr").removeAttr('readonly');
        $("#qr").val('');
        $("#qr").focus();
    }

    $("#qr").on("keyup", "input", function(e) {
        if($("#qr").attr("readonly") == "readonly") {
            switch(e.which) {
                case 8:     // BACKSPACE
                    var initial = $(this).val();
                    var reduced = initial.substring(0, initial.length-1);
                    $(this).val(reduced);
                break;
                default:    // LETTER OR NUMBER
                    if(e.key.match(/^[A-Z0-9]$/)) {
                        var initial = $(this).val();
                        $(this).val(initial + e.key);
                    }
                break;
            }
        }
    });


});