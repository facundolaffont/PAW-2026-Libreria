<?php

use Phinx\Migration\AbstractMigration;

class AddPrecioToReservaTables extends AbstractMigration
{
    public function change(): void
    {
        // Snapshot del precio al momento de la reserva (los precios pueden cambiar después).
        $this->table('reserva_libros')
            ->addColumn('precio_unitario', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => 0,
                'after'     => 'cantidad',
            ])
            ->update();

        // Total de la reserva al momento de confirmar (suma de precio_unitario * cantidad).
        $this->table('reservas')
            ->addColumn('total', 'decimal', [
                'precision' => 10,
                'scale'     => 2,
                'default'   => 0,
                'after'     => 'telefono',
            ])
            ->update();
    }
}
