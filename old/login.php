<!DOCTYPE html>
<html lang="es">
    <head>
        <title>ControlAppQR</title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">                   <!-- SIRVE PARA INTERNET EXPLORER -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- FORMA EN QUE SE VE LA VISTA EN MOVIL-->

        <!-- BOOTSTRAP CSS -->
        <link href="css/ControlAppQR.css" rel="stylesheet">
        <link href="css/bootstrap.css" rel="stylesheet">
    </head>
    <body>
        <div id="login" class="container-fluid text-center mt-5 pt-5">
            <form method="POST" action="checklogin.php" class="mt-5 pt-5">
                <div class="row">
                    <div class="col-md-4 col-sm-6"></div>
                    <div class="col-md-4 col-sm-6">
                        <fieldset class="text-center">
                            <!--<div><img src="../img/logo.jpg" alt="logo empresa" width="300px"/></div><br>-->
                                <input type="email" class="form-control w-50 mx-auto" name="email" placeholder="Email" required>
                                <input type="password" class="form-control w-50 mt-2 mx-auto" name="pass" placeholder="Contraseña" required>
                            
                                <div><button type="submit" class="btn btn-primary mt-3 mx-auto" id="btn_login">Iniciar sesión</button></div>
                        </fieldset>
                    </div>
                </div>
            </form>
        </div>
    </body >
</html>
