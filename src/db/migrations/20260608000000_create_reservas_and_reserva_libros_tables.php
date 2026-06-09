<?php

use Phinx\Migration\AbstractMigration;

class CreateReservasAndReservaLibrosTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('reservas')
            ->addColumn('nombre',     'string',    ['limit' => 100])
            ->addColumn('email',      'string',    ['limit' => 254])
            ->addColumn('telefono',   'string',    ['limit' => 20])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'])
            ->create();

        // INT UNSIGNED para coincidir con la PK 'id' (Phinx la genera así).
        // De lo contrario las FKs fallan con error 3780 (tipos incompatibles).
        $this->table('reserva_libros')
            ->addColumn('reserva_id', 'integer', ['signed' => false])
            ->addColumn('book_id',    'integer', ['signed' => false, 'null' => true])
            ->addColumn('titulo',     'string',    ['limit' => 255])
            ->addColumn('autor',      'string',    ['limit' => 255])
            ->addColumn('cantidad',   'integer',   ['default' => 1])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['reserva_id'])
            ->addIndex(['book_id'])
            ->addForeignKey('reserva_id', 'reservas', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('book_id',    'books',    'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();
    }
}
