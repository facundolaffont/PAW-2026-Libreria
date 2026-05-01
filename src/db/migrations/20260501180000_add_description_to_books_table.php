<?php

use Phinx\Migration\AbstractMigration;

class AddDescriptionToBooksTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('books');
        $table->addColumn('description', 'text', ['null' => true, 'after' => 'genre'])
              ->update();
    }
}
