<?php

use Phinx\Migration\AbstractMigration;

class CreateReservationsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('reservations');
        $table->addColumn('nombre',     'string',    ['limit' => 100])
              ->addColumn('email',      'string',    ['limit' => 254])
              ->addColumn('telefono',   'string',    ['limit' => 20])
              ->addColumn('libros',     'text')
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->create();
    }
}
