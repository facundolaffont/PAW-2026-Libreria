<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PAWPrints' ?></title>

    <!-- Reset CSS -->
    <link rel="stylesheet" href="resources/styles/reset.css">

    <!-- Estilo base -->
    <link rel="stylesheet" href="resources/styles/base.css">

    <!-- Estilo de la página -->
    <link rel="stylesheet" href="resources/styles/<?= $page ?>.css">

    <script src="resources/js/components/HamburgerMenu.js"></script>
    <script>
        // Cuando termina de cargar el DOM, crea el menú hamburguesa.
        document.addEventListener('DOMContentLoaded', () => {
            const hamburgerButton = document.querySelector('button.menu-hamburguesa');
            const menu = document.querySelector('nav.menu-navegacion');
            new HamburgerMenu(hamburgerButton, menu);
        });
    </script>

</head>