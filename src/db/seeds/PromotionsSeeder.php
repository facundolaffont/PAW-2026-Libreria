<?php

use Phinx\Seed\AbstractSeed;

class PromotionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->execute('TRUNCATE TABLE promotions');

        $promotions = [
            ['description' => 'Promoción de verano: 20% de descuento en novelas', 'image' => 'placeholder-promoción-grande.png'],
            ['description' => 'Semana del libro: envío gratis en compras mayores a $3000', 'image' => 'placeholder-promoción-grande.png'],
            ['description' => '2x1 en libros de ciencia ficción', 'image' => 'placeholder-promoción-grande.png'],
        ];

        $this->table('promotions')->insert($promotions)->saveData();
    }
}
