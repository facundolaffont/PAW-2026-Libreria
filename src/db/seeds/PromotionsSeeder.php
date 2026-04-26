<?php

use Phinx\Seed\AbstractSeed;

class PromotionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->execute('TRUNCATE TABLE promotions');

        $promotions = [
            ['description' => 'Promoción de verano: 20% de descuento en novelas', 'image' => '599:placeholder-promoción-chica.png;placeholder-promoción-grande.png'],
            ['description' => 'Semana del libro: envío gratis en compras mayores a $3000', 'image' => '599:placeholder-promoción-chica.png;placeholder-promoción-grande.png'],
            ['description' => '2x1 en libros de ciencia ficción', 'image' => '599:placeholder-promoción-chica.png;placeholder-promoción-grande.png'],
        ];

        $this->table('promotions')->insert($promotions)->saveData();
    }
}
