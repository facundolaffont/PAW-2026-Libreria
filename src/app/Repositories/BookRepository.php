<?php

    namespace Paw\Repositories;

    use Paw\Interfaces\BookRepositoryInterface;
    
    class BookRepository implements BookRepositoryInterface {
        public function __construct(private \PDO $db) {}

        public function findAllGroupedByGenre(): array {
            $stmt = $this->db->query('SELECT id, title, author, genre, price, image FROM books ORDER BY genre, title');
            $booksByGenre = [];
            foreach ($stmt->fetchAll() as $book) {
                $booksByGenre[$book['genre']][] = $book;
            }
            return $booksByGenre;
        }

        public function findAllGenres(): array {
            $stmt = $this->db->query('SELECT DISTINCT genre FROM books ORDER BY genre');
            return array_column($stmt->fetchAll(), 'genre');
        }

        public function findAll(int $offset, int $limit, array $filters = []): array {
            ['clause' => $where, 'params' => $params] = $this->buildWhere($filters);
            $stmt = $this->db->prepare(
                "SELECT id, title, author, genre, price, image FROM books {$where} ORDER BY title LIMIT :limit OFFSET :offset"
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        public function countAll(array $filters = []): int {
            ['clause' => $where, 'params' => $params] = $this->buildWhere($filters);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM books {$where}");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        }

        /**
         * Construye la cláusula WHERE y los parámetros para las consultas de libros según los filtros aplicados.
         * 
         * @param array $filters Los filtros aplicados (géneros, rango de precios,  autor).
         * @return array Un arreglo con la cláusula WHERE y los parámetros correspondientes.    
         */
        private function buildWhere(array $filters): array {
            $where  = [];
            $params = [];

            if (!empty($filters['generos'])) {
                $placeholders = [];
                foreach ($filters['generos'] as $i => $g) {
                    $key = ":genero{$i}";
                    $placeholders[] = $key;
                    $params[$key]   = $g;
                }
                $where[] = 'genre IN (' . implode(', ', $placeholders) . ')';
            }
            if (isset($filters['precio_min']) && $filters['precio_min'] !== '') {
                $where[]              = 'price >= :precio_min';
                $params[':precio_min'] = (float)$filters['precio_min'];
            }
            if (isset($filters['precio_max']) && $filters['precio_max'] !== '') {
                $where[]              = 'price <= :precio_max';
                $params[':precio_max'] = (float)$filters['precio_max'];
            }
            if (!empty($filters['autor'])) {
                $where[]         = 'author LIKE :autor';
                $params[':autor'] = '%' . $filters['autor'] . '%';
            }

            return [
                'clause' => $where ? 'WHERE ' . implode(' AND ', $where) : '',
                'params' => $params,
            ];
        }
    }