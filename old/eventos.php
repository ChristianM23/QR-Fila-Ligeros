<?php if(!isset($incluir)) header("Location: index.php", true, 301); ?>

<section class="container-fluid">
    <div class="row">
        <div class="col-md-9 col-sm-12 ms-4">
            <h2>Eventos</h2>
        </div>
        <div class="col-md-2 col-sm-12">
            <button id="btn_reg_evento" class="btn btn_color enlace" data-enlace="reg_evento">Nuevo evento</button>
        </div>
    </div>

    <div class="text-center mt-4">    
        <div class="mt-2">
            <table class="datatable table table-striped" data-order='[[0, "desc"]]' data-page-length='50'>
                <thead class="fw-bold text-center cursor-pointer">
                    <tr>
                        <th>Evento</th>
                        <th>Fecha_ini</th>
                        <th>Activo</th>
                        <th>Apuntados</th>
                        <th>Asistentes</th>
                    </tr>
                </thead>
                <tbody id="datos_eventos" class="text-center"></tbody>
            </table>
        </div>
    </div>
</section>