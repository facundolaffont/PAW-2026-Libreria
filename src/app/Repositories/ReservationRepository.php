<?php

    namespace Paw\Repositories;

    use Paw\Interfaces\ReservationRepositoryInterface;
    use Psr\Log\LoggerInterface;

    class ReservationRepository implements ReservationRepositoryInterface {

        public function __construct(private \PDO $db, private LoggerInterface $logger) {}

        public function create(array $cliente, array $items): int {
            $this->logger->debug("", ['cliente' => $cliente, 'items_count' => count($items)]);

            try {
                $this->db->beginTransaction();

                $total = 0.0;
                foreach ($items as $item) {
                    $total += ((float)($item['precio_unitario'] ?? 0)) * (int)$item['cantidad'];
                }

                $stmt = $this->db->prepare(
                    'INSERT INTO reservas (nombre, email, telefono, total)
                     VALUES (:nombre, :email, :telefono, :total)'
                );
                $stmt->bindValue(':nombre',   $cliente['nombre']);
                $stmt->bindValue(':email',    $cliente['email']);
                $stmt->bindValue(':telefono', $cliente['telefono']);
                $stmt->bindValue(':total',    $total);
                $stmt->execute();

                $reservaId = (int) $this->db->lastInsertId();

                $stmtItem = $this->db->prepare(
                    'INSERT INTO reserva_libros (reserva_id, book_id, titulo, autor, cantidad, precio_unitario)
                     VALUES (:reserva_id, :book_id, :titulo, :autor, :cantidad, :precio_unitario)'
                );
                foreach ($items as $item) {
                    $stmtItem->bindValue(':reserva_id', $reservaId, \PDO::PARAM_INT);
                    $stmtItem->bindValue(
                        ':book_id',
                        isset($item['book_id']) && $item['book_id'] > 0 ? (int) $item['book_id'] : null,
                        isset($item['book_id']) && $item['book_id'] > 0 ? \PDO::PARAM_INT : \PDO::PARAM_NULL
                    );
                    $stmtItem->bindValue(':titulo',          $item['titulo']);
                    $stmtItem->bindValue(':autor',           $item['autor']);
                    $stmtItem->bindValue(':cantidad',        (int) $item['cantidad'], \PDO::PARAM_INT);
                    $stmtItem->bindValue(':precio_unitario', (float)($item['precio_unitario'] ?? 0));
                    $stmtItem->execute();
                }

                $this->db->commit();
                $this->logger->info("Reserva persistida.", ['reserva_id' => $reservaId]);

                return $reservaId;

            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $this->logger->error("Error al persistir reserva.", ['error' => $e->getMessage()]);
                throw $e;
            }
        }
    }
