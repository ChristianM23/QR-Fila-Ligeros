<?php if(!isset($incluir)) header("Location: index.php", true, 301); ?>

<section class="container-fluid text-center">
    <div class="text-center mt-5 pt-5">
        <div>
            <!--<i id="imgCodigoQR" class="bi bi-qr-code-scan icono"></i>-->
            <input id="qr" type="text" class="form-control d-inline text-center w-25 mx-4">
            <!--<i id="imgEscribir" class="bi bi-cursor-text icono"></i>-->
        </div>

        <button id="btn_guardar" class="btn btn-info mt-3">Guardar</button>
        <br>
        <button id="btn_enviar" class="btn btn_color mt-5">Registrar lista</button>
    </div>
    <div class="w-50 mx-auto text-center mt-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nombre</th>
                    <th>Figura</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</section>