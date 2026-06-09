<?php

use Phinx\Seed\AbstractSeed;

class PromotionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->execute('TRUNCATE TABLE promotions');

        $promotions = [
            ['description' => 'Promoción de verano: 20% de descuento en novelas', 'image' => 'promo-verano.svg'],
            ['description' => 'Semana del libro: envío gratis en compras mayores a $3000', 'image' => 'promo-semana-libro.svg'],
            ['description' => '2x1 en libros de ciencia ficción', 'image' => 'promo-2x1-scifi.svg'],
        ];

        $this->table('promotions')->insert($promotions)->saveData();
    }
}
