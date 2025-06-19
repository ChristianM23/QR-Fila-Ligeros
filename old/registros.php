<?php if(!isset($incluir)) header("Location: index.php", true, 301); ?>

<section class="container-fluid">
    <div class="row">
        <div class="col-9 ms-4">
            <h2>Registros</h2>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <div class="mt-2">
            <table class="datatable table table-striped" data-order='[[0, "desc"]]' data-page-length='50'>
                <thead class="fw-bold text-center cursor-pointer">
                    <tr>
                        <th>Num_Fila</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Figura</th>
                        <th>Evento</th>
                        <th>Fecha entrada</th>
                    </tr>
                </thead>
                <tbody id="datos_reg" class="text-center"></tbody>
            </table>
        </div>
    </div>
</section>