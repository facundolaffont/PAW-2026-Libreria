<!DOCTYPE html>
<html lang="es">
    <?php
        require 'head.php';

        use Paw\Errors\Exceptions\AppException;

        if(isset($page)) {
            require "../views/{$page}.php";
        } else {
            throw new AppException(
                "Error: no se ha definido el contenido del cuerpo de la página."
            );
        }
    ?>
</html>