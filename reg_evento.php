<?php if(!isset($incluir)) header("Location: index.php", true, 301); ?>
<!-- Pestaña de registro/actualización de eventos -->
<section class="container-fluid">
    <div class="row">
        <div class="col-9 ms-4">
            <h2 class="titulo">Nuevo evento</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4"></div>
        <div class="col-md-4 col-sm-12">
            <input type="text" class="form-control" id="evento" placeholder="Evento" required>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-4"></div>
        <div class="col-md-2 col-sm-6">
            <input type="date" class="form-control" id="fecha" placeholder="Fecha inicio">
        </div>
        <div class="col-md-2 col-sm-6">
            <input type="time" class="form-control resp_margin_top" id="hora" placeholder="Hora inicio">
        </div>
    </div>

    <div class="text-center mt-4"><input type="checkbox" id="activo" value="1"> <label for="activo">Activo</label></div>

    <div class="text-center mt-4"><button type="submit" class="btn btn_color">Registrar</button></div>
</section>