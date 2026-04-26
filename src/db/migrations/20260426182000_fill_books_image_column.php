<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class FillBooksImageColumn extends AbstractMigration
{
    public function up(): void
    {
        // Asigna el valor por defecto a todos los libros que tengan image NULL o vacía
        $this->execute(
            "UPDATE books SET image = '599:placeholder-libro-chica.png;placeholder-libro-grande.png' WHERE image IS NULL OR image = ''"
        );
    }

    public function down(): void
    {
        // Deja la columna image en NULL si se revierte
        $this->execute(
            "UPDATE books SET image = NULL"
        );
    }
}
