<?php if(!isset($incluir)) header("Location: index.php", true, 301); ?>
<!-- Pestaña de registro/actualización de usuario -->
<section class="container-fluid">
    <div class="row">
        <div class="col-9 ms-4">
            <h2 class="titulo">Nuevo usuario</h2>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3"></div>
        <div class="col-md-3 col-sm-12">
            <input type="text" class="form-control" id="nombre" placeholder="Nombre" required>
        </div>
        <div class="col-md-3 col-sm-12 resp_margin_top">
            <input type="text" class="form-control" id="apellidos" placeholder="Apellidos" required>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3"></div>
        <div class="col-md-1 col-sm-12"><input type="number" class="form-control" id="num_fila" placeholder="Nº filà"></div>
        <div class="col-md-2 col-sm-12 resp_margin_top">
            <select class="form-control" id="figura" placeholder="Figura" required>
                <option value="">Figura</option>
                <option value="Supernumerari">Supernumerari</option>
                <option value="Veterano A">Veterano A</option>
                <option value="Veterano B">Veterano B</option>
                <option value="Fester">Fester</option>
                <option value="Socio">Socio</option>
                <option value="Acompañante">Acompañante</option>
                <option value="Juvenil">Juvenil</option>
                <option value="Infantil">Infantil</option>
            </select>
        </div>
        <div class="col-md-3 col-sm-12 resp_margin_top">
            <select class="form-control" id="cargo" placeholder="Cargo">
                <option value="10">Cargo</option>
                <option value="3">Primer tro</option>
                <option value="4">Darrer tro</option>
                <option value="5">Secretari</option>
                <option value="6">Vice secretari</option>
                <option value="7">Tresorer</option>
                <option value="8">Contador</option>
                <option value="9">Vocal</option>
            </select>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-3"></div>
        <div class="col-md-4 col-sm-12">
            <input type="text" class="form-control" id="email" placeholder="Email" required>
        </div>
        <div class="col-md-2 col-sm-12 resp_margin_top">
            <input type="text" class="form-control" id="telefono" placeholder="Telefono">
        </div>
    </div>

    <div class="text-center mt-4"><button type="submit" class="btn btn_color">Registrar</button></div>
</section>