<?php

    namespace Paw\Repositories;

    use Paw\Interfaces\BookRepositoryInterface;
    
    class BookRepository implements BookRepositoryInterface {
        public function __construct(private \PDO $db) {}

        public function findAllGroupedByGenre(): array {
            $stmt = $this->db->query('SELECT id, title, author, genre, price FROM books ORDER BY genre, title');
            $booksByGenre = [];
            foreach ($stmt->fetchAll() as $book) {
                $booksByGenre[$book['genre']][] = $book;
            }
            return $booksByGenre;
        }
    }