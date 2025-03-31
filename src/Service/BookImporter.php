<?php

namespace App\Service;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;

class BookImporter
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function importFromJson(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \Exception("El archivo no existe en la ruta: $filePath");
        }

        $jsonData = file_get_contents($filePath);
        if ($jsonData === false) {
            throw new \Exception("Error al leer el archivo JSON: $filePath");
        }

        $booksArray = json_decode($jsonData, true);
        if ($booksArray === null) {
            throw new \Exception("Error al leer el archivo JSON o el formato no es válido.");
        }

        foreach ($booksArray['books'] as $bookData) {
            if (!isset($bookData['isbn'], $bookData['title'], $bookData['author'], $bookData['published'])) {
                throw new \Exception("Faltan datos obligatorios para el libro: " . json_encode($bookData));
            }

            $book = new Book();
            $book->setIsbn($bookData['isbn']);
            $book->setTitle($bookData['title']);
            $book->setSubtitle($bookData['subtitle'] ?? null);
            $book->setAuthor($bookData['author']);

            // Manejo de la fecha de publicación
            try {
                $publishedDate = $bookData['published'];

                // Eliminar "Z" si existe
                if (strpos($publishedDate, 'Z') !== false) {
                    $publishedDate = str_replace('Z', '', $publishedDate);
                }

                // Convertir a DateTime
                $date = new \DateTime($publishedDate);
                $book->setPublished($date);
            } catch (\Exception $e) {
                throw new \Exception("Error al procesar la fecha de publicación: " . $e->getMessage());
            }

            $book->setPublisher($bookData['publisher'] ?? null);
            $book->setDescription($bookData['description'] ?? null);
            $book->setWebsite($bookData['website'] ?? null);
            $book->setCategory($bookData['category'] ?? null);

            if (isset($bookData['pages']) && is_numeric($bookData['pages'])) {
                $book->setPages((int)$bookData['pages']);
            }

            // Persistir el objeto Book en la base de datos
            $this->entityManager->persist($book);
        }

        // Guardar todos los libros en la base de datos
        $this->entityManager->flush();
    }
}
