<?php 
    header("Content-type: application/javascript; charset=utf-8", true); 
    session_start();
    include('../config/config.php');
?>
$(document).ready(function() {

    $.ajax({
        url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&c=registros",
        type: 'post',
        dataType: 'json'
    }).done(function(registro) {
        $('#datos_reg').html("");
        for(var i = 0; i < registro.length; i++) {
            var dato =  
                "<tr data-id='"+ registro[i].Id +"'>"
                +   "<td>"+ registro[i].Num_fila +"</td>"
                +   "<td>"+ registro[i].Nombre +"</td>"
                +   "<td>"+ registro[i].Apellidos +"</td>"
                +   "<td>"+ registro[i].Figura +"</td>"
                +   "<td>"+ registro[i].Evento +"</td>"
                +   "<td>"+ registro[i].Fecha +"</td>"
                +"</tr>";
            $('#datos_reg').append(dato);
        }

        $(".datatable").DataTable({
            paging: false,
            "language": {"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"}
        });

    });
  
});