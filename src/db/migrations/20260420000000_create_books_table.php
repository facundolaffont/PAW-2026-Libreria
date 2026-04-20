<?php

use Phinx\Migration\AbstractMigration;

class CreateBooksTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('books');
        $table->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('author', 'string', ['limit' => 255])
              ->addColumn('isbn', 'string', ['limit' => 13, 'null' => true])
              ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2])
              ->addColumn('stock', 'integer', ['default' => 0])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['isbn'], ['unique' => true])
              ->create();
    }
}
