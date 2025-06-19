<?php if(!isset($incluir)) header("Location: index.php", true, 301); ?>

<section class="container-fluid">
    <div class="row">
        <div class="col-md-9 col-sm-12 ms-4">
            <h2>Usuarios</h2>
        </div>
        <div class="col-md-2 col-sm-12">
            <button id="btn_reg_usuario" class="btn btn_color enlace" data-enlace="reg_usuario">Nuevo usuario</button>
        </div>
    </div>

    <div class="text-center mx-auto mt-4">
        <div class="mt-2">
            <table class="datatable table table-striped" data-order='[[0, "desc"]]' data-page-length='100'>
                <thead class="fw-bold text-center cursor-pointer">
                    <tr>
                        <th>Nº filá</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Figura</th>
                    </tr>
                </thead>
                <tbody id="datos_usuarios" class="text-center"></tbody>
            </table>
        </div>
    </div>
</section>