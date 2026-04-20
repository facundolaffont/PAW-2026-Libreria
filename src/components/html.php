<!DOCTYPE html>
<html lang="es">
    <?php
        require 'head.php';

        if(isset($page)) {
            require "../views/{$page}.php";
        } else {
            throw new Exception("Error: no se ha definido el contenido del cuerpo de la página.");
        }
    ?>
</html>