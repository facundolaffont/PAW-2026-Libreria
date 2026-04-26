<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class UpdatePromotionsImageFormat extends AbstractMigration
{
    public function up(): void
    {
        // Actualiza todas las promociones al nuevo formato de imagen
        $this->execute(
            "UPDATE promotions SET image = '599:placeholder-promoción-chica.png;placeholder-promoción-grande.png'"
        );
    }

    public function down(): void
    {
        // Si quisieras revertir, podrías dejar el campo con solo la imagen grande
        $this->execute(
            "UPDATE promotions SET image = 'placeholder-promoción-grande.png'"
        );
    }
}
