<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PAWPrints' ?></title>

    <!-- Reset CSS -->
    <link rel="stylesheet" href="resources/styles/reset.css">

    <!-- Estilo base -->
    <link rel="stylesheet" href="resources/styles/base.css">

    <?php if (isset($pageStyle)): ?>
        <!-- Estilo de la página -->
        <link rel="stylesheet" href="resources/styles/<?= $pageStyle ?>">
    <?php endif; ?>

</head>