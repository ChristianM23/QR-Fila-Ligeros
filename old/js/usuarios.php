<?php 
    header("Content-type: application/javascript; charset=utf-8", true); 
    session_start();
    include('../config/config.php');
?>
$(document).ready(function() {

    $.ajax({
        url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&c=usuarios",
        type: 'post',
        dataType: 'json'
    }).done(function(usuario) {
        $('#datos_usuarios').html("");
        for(var i = 0; i < usuario.length; i++) {
            var dato =  
                "<tr class='enlace' data-id='"+ usuario[i].Id +"' data-enlace='reg_usuario'>"
                +   "<td>"+ usuario[i].Num_fila +"</td>"
                +   "<td>"+ usuario[i].Nombre +"</td>"
                +   "<td>"+ usuario[i].Apellidos +"</td>"
                +   "<td>"+ usuario[i].Figura +"</td>"
                +"</tr>";
            $('#datos_usuarios').append(dato);
        }

        $(".datatable").DataTable({
            paging: false,
            "language": {"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"}
        });
    });
  
});