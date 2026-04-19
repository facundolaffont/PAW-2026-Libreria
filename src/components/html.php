<!DOCTYPE html>
<html lang="es">
    <?php
        require 'head.php';

        if(isset($bodyContent)) {
            require "../views/{$bodyContent}.php";
        } else {
            die("Error: No se ha definido el contenido del cuerpo de la página.");
        }
    ?>
</html>