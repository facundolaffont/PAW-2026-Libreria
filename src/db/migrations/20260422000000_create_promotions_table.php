<?php

use Phinx\Migration\AbstractMigration;

class CreatePromotionsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('promotions');
        $table->addColumn('description', 'string', ['limit' => 255])
              ->addColumn('image', 'string', ['limit' => 255])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->create();
    }
}
