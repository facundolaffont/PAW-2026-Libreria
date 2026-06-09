<body>

    <?php require 'components/header.php'; ?>

    <main>

        <h1>Pedidos</h1>

        <p class="orders-total">
            <?= (int)($context['total'] ?? 0) ?> pedido<?= ($context['total'] ?? 0) !== 1 ? 's' : '' ?> en total
        </p>

        <?php if (empty($context['orders'])): ?>
            <p class="orders-vacio">Todavía no hay pedidos registrados.</p>
        <?php else: ?>

            <div class="orders-tabla-wrapper" role="region" aria-label="Listado de pedidos" tabindex="0">
                <table class="orders-tabla">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Email</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Libros</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($context['orders'] as $order): ?>
                            <tr>
                                <td><?= (int)$order['id'] ?></td>
                                <td>
                                    <time datetime="<?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(
                                            date('d/m/Y H:i', strtotime($order['created_at'])),
                                            ENT_QUOTES, 'UTF-8'
                                        ) ?>
                                    </time>
                                </td>
                                <td><?= htmlspecialchars($order['nombre'],   ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($order['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (!empty($order['libros'])): ?>
                                        <ul class="orders-libros">
                                            <?php foreach ($order['libros'] as $libro): ?>
                                                <li>
                                                    <span class="orders-libro-titulo">
                                                        <?= htmlspecialchars($libro['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                    <?php if (!empty($libro['autor'])): ?>
                                                        <span class="orders-libro-autor">
                                                            — <?= htmlspecialchars($libro['autor'], ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="orders-sin-libros">Sin libros</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (($context['totalPages'] ?? 1) > 1): ?>
                <nav aria-label="Paginación de pedidos">
                    <ol class="paginacion">
                        <?php if ($context['currentPage'] > 1): ?>
                            <li>
                                <a href="orders?pagina=<?= $context['currentPage'] - 1 ?>" aria-label="Página anterior">
                                    Anterior
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $context['totalPages']; $i++): ?>
                            <li>
                                <a
                                    href="orders?pagina=<?= $i ?>"
                                    aria-label="Página <?= $i ?>"
                                    <?= $i === $context['currentPage'] ? 'aria-current="page"' : '' ?>
                                ><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($context['currentPage'] < $context['totalPages']): ?>
                            <li>
                                <a href="orders?pagina=<?= $context['currentPage'] + 1 ?>" aria-label="Página siguiente">
                                    Siguiente
                                </a>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <?php require 'components/footer.php'; ?>

</body>
