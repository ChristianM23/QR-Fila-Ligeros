<?php 
    header("Content-type: application/javascript; charset=utf-8", true); 
    session_start();
    include('../config/config.php');
?>
$(document).ready(function() {

    $.ajax({
        url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&c=eventos",
        type: 'post',
        dataType: 'json'
    }).done(function(evento) {
        $('#datos_eventos').html("");
        console.log(evento);
        for(var i = 0; i < evento.length; i++) {
            if(evento[i]['Activo'] == 1) var str_activar = "Activo";
            else var str_activar = "";

            var dato =  
                "<tr class='enlace' data-id='"+ evento[i].Id +"' data-enlace='reg_evento'>"
                +   "<td>"+ evento[i].Evento +"</td>"
                +   "<td>"+ evento[i].Fecha +"</td>"
                +   "<td>"+ str_activar +"</td>"
                +   "<td>"+ evento[i].Apuntados +"</td>"
                +   "<td>"+ evento[i].Asistentes +"</td>"
                +"</tr>";
            $('#datos_eventos').append(dato);
        }

        $(".datatable").DataTable({
            paging: false,
            "language": {"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"}
        });
    });
  
});