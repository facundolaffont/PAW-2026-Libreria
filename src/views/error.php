<body>

    <main>
        <h1><?= htmlspecialchars((string)$context['httpCode']) ?></h1>
        <p><?= htmlspecialchars($context['clientMessage']) ?></p>
        <a href="/">Volver al inicio</a>
    </main>

</body>
