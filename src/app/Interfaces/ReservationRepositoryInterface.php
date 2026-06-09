<?php

    namespace Paw\Interfaces;

    interface ReservationRepositoryInterface {

        /**
         * Persiste una reserva con sus libros asociados en una sola transacción.
         *
         * @param array $cliente ['nombre' => string, 'email' => string, 'telefono' => string]
         * @param array $items   Lista de ['book_id' => int|null, 'titulo' => string, 'autor' => string, 'cantidad' => int]
         * @return int ID de la reserva creada.
         */
        public function create(array $cliente, array $items): int;
    }
