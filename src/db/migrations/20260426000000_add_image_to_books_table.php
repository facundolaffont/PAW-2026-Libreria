<?php

use Phinx\Migration\AbstractMigration;

class AddImageToBooksTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('books');
        $table->addColumn('image', 'string', [
                  'limit' => 500,
                  'null' => true,
                  'default' => '599:placeholder-libro-chica.png;placeholder-libro-grande.png',
                  'after' => 'genre',
              ])
              ->update();
    }
}
