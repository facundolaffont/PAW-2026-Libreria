<?php

    namespace Paw\Repositories;

    use Paw\Interfaces\BookRepositoryInterface;
    use Psr\Log\LoggerInterface;
    
    class BookRepository implements BookRepositoryInterface {
        
        public function __construct(private \PDO $db, private LoggerInterface $logger) {}

        public function findAllGroupedByGenre(): array {
            $this->logger->debug("");

            $stmt = $this->db->query('SELECT id, title, author, genre, price, image FROM books ORDER BY genre, title');
            $booksByGenre = [];
            foreach ($stmt->fetchAll() as $book) {
                $booksByGenre[$book['genre']][] = $book;
            }
            return $booksByGenre;
        }

        public function findAllGenres(): array {
            $this->logger->debug("");

            $stmt = $this->db->query('SELECT DISTINCT genre FROM books ORDER BY genre');
            return array_column($stmt->fetchAll(), 'genre');
        }

        public function findAllAuthors(): array {
            $this->logger->debug("");

            $stmt = $this->db->query('SELECT DISTINCT author FROM books ORDER BY author');
            return array_column($stmt->fetchAll(), 'author');
        }

        public function findAll(int $offset, int $limit, array $filters = []): array {
            $this->logger->debug("",
                compact('offset', 'limit', 'filters')
            );

            ['clause' => $where, 'params' => $params] = $this->buildWhere($filters);
            $orderBy = $this->buildOrderBy($filters['orden'] ?? '');
            $stmt = $this->db->prepare(
                "SELECT id, title, author, genre, price, image FROM books {$where} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset"
            );
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        /**
         * Encuentra un libro por su ID.
         * 
         * @param int $id El ID del libro.
         * @return array|null El libro encontrado, o null si no existe.
         */
        public function findById(int $id): ?array {
            $this->logger->debug("", compact('id'));

            $this->logger->debug("Ejecutando consulta para encontrar libro por ID {$id}...");
            $stmt = $this->db->prepare(
                'SELECT id, title, description, author, genre, price, image FROM books WHERE id = :id'
            );
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $book = $stmt->fetch();
            $this->logger->debug("Consulta ejecutada.", compact('book'));

            return $book ?: null;
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
                $placeholders = [];
                foreach ($filters['autor'] as $i => $a) {
                    $key = ":autor{$i}";
                    $placeholders[] = $key;
                    $params[$key]   = $a;
                }
                $where[] = 'author IN (' . implode(', ', $placeholders) . ')';
            }
            if (!empty($filters['q'])) {
                $where[]     = '(title LIKE :q OR author LIKE :q OR genre LIKE :q)';
                $params[':q'] = '%' . $filters['q'] . '%';
            }

            return [
                'clause' => $where ? 'WHERE ' . implode(' AND ', $where) : '',
                'params' => $params,
            ];
        }

        public function create(array $data): int {
            $stmt = $this->db->prepare(
                'INSERT INTO books (title, author, genre, price, stock, isbn, description, image)
                 VALUES (:title, :author, :genre, :price, :stock, :isbn, :description, :image)'
            );
            $stmt->bindValue(':title',       $data['title']);
            $stmt->bindValue(':author',      $data['author']);
            $stmt->bindValue(':genre',       $data['genre'] ?? null);
            $stmt->bindValue(':price',       (float) $data['price']);
            $stmt->bindValue(':stock',       (int)   $data['stock'],   \PDO::PARAM_INT);
            $stmt->bindValue(':isbn',        $data['isbn'] ?? null);
            $stmt->bindValue(':description', $data['description'] ?? null);
            $stmt->bindValue(':image',       $data['image'] ?? null);
            $stmt->execute();
            return (int) $this->db->lastInsertId();
        }

        public function findPriceMin(): float {
            return (float) $this->db->query('SELECT MIN(price) FROM books')->fetchColumn();
        }

        public function findPriceMax(): float {
            return (float) $this->db->query('SELECT MAX(price) FROM books')->fetchColumn();
        }

        public function existsByIsbn(string $isbn): bool {
            $stmt = $this->db->prepare('SELECT 1 FROM books WHERE isbn = :isbn LIMIT 1');
            $stmt->bindValue(':isbn', $isbn);
            $stmt->execute();
            return (bool) $stmt->fetchColumn();
        }

        /**
         * Traduce el parámetro `orden` (whitelist) a una cláusula ORDER BY segura.
         * Nunca interpola el valor crudo en SQL.
         */
        private function buildOrderBy(string $orden): string {
            return match ($orden) {
                'precio-asc'  => 'price ASC',
                'precio-desc' => 'price DESC',
                'titulo-desc' => 'title DESC',
                'autor-asc'   => 'author ASC, title ASC',
                'recientes'   => 'id DESC',
                default       => 'title ASC',
            };
        }
    }