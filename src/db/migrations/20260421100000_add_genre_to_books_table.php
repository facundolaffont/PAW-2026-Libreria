<?php

use Phinx\Migration\AbstractMigration;

class AddGenreToBooksTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('books');
        $table->addColumn('genre', 'string', ['limit' => 100, 'null' => true, 'after' => 'author'])
              ->update();
    }
}
