<?php 
header("Content-type: application/javascript; charset=utf-8", true); 
session_start();
include('../config/config.php');
?>
$(document).ready(function() {
    //Carga paginas
    $(document).on('click','.enlace', function() {  
        var f = "";
        var d = "";
        var i = "";
        var pagina = "_self";
              
        if($(this).data("id")) {
            var i = "&u="+ $(this).data("id");
            var pagina = "_blank";
        }
        
        var str = "index.php?p="+ $(this).data("enlace");
        if(i != "") str += i;
        if(f != "") str += f;
        if(d != "") str += d;
        window.open(str, pagina);     
    });

    //Responsive menú hamburguesa
    $(document).on('click', '#menu_hamburguesa', function() {
        if($("#pestanyas li").hasClass("responsive")) {
            console.log('1');
            $("#pestanyas li").removeClass("responsive");
            $("#pestanyas li").addClass("desplegable");
        } else if($("#pestanyas li").hasClass("desplegable")) {
            console.log('2');
            $("#pestanyas li").removeClass("desplegable");
            $("#pestanyas li").addClass("responsive");
        }
    });

    //Consulta color fila
    $.ajax({
        url: "<?= $IP ?>api-qr.php?e=<?= $_SESSION['email'] ?>&p=<?= $_SESSION['token'] ?>&c=color_fila",
        type: 'POST'
    }).done(function(color) {
        var estilo_bg = {"background-color": color};
        var estilo_color = {"color": "white"};
        
        $("nav, #pestanyas li, .btn_color").css(estilo_bg);
        $(".btn_color").css(estilo_color);        
    });

});