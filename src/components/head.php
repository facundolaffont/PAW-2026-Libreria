<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'PAWPrints', ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Reset CSS -->
    <link rel="stylesheet" href="resources/styles/reset.css">

    <!-- Estilo base -->
    <link rel="stylesheet" href="resources/styles/base.css">

    <!-- Estilo de la página -->
    <link rel="stylesheet" href="resources/styles/<?= $page ?>.css">

    <script src="resources/js/app.js"></script>

</head>
