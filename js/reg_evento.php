<?php 
    header("Content-type: application/javascript; charset=utf-8", true); 
    session_start();
    include('../config/config.php');
?>
$(document).ready(function() {
    if($(location).attr('href').includes("u=")) {
        $("button").html("Actualizar");
        $(".titulo").html("Actualizar evento");
    }

    if($(location).attr('href').includes("u=")) {
        $.ajax({
            url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&c=info_evento",
            type: 'post',
            data: {id_evento:$(location).attr('href').split("u=")[1]}
        }).done(function(evento) {
            var obj = JSON.parse(evento);
            
            $("#evento").val(obj[0].Evento);
            $("#fecha").val(obj[0].Fecha);
            $("#hora").val(obj[0].Hora);
            if(obj[0].Activo == 1) $("#activo").attr("checked", "checked");
        });
    }

    $("button").on("click", function() {
        var url = "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&i=evento";
        var id_evento = "0";
        if($(location).attr('href').includes("u=")) {
            url = "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&u=evento";
            id_evento = $(location).attr('href').split("u=")[1];
        }
        if($("#activo").is(":checked")) var activo = 1;
        else var activo = 0;

        $.ajax({
            url: url,
            type: 'post',
            data: {id_evento:id_evento, evento:$("#evento").val(), fecha:$("#fecha").val(), hora:$("#hora").val(), activo:activo}
        }).done(function(evento) {
            var mensaje = "Evento registrado.";
            if($(location).attr('href').includes("u=")) var mensaje = "Datos actualizados.";

            if(evento == 1) {
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