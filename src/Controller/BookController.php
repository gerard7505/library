<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/import-books', name: 'import_books')]
    public function importBooks(): Response
    {
       
        $filePath = '/home/usuario/Descargas/books.json'; 

      
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
            $book->setPublished($bookData['published']);
            $book->setPublisher($bookData['publisher'] ?? null);
            $book->setDescription($bookData['description'] ?? null);
            $book->setWebsite($bookData['website'] ?? null);

           
            $book->setCategory($bookData['category'] ?? null);

            if (isset($bookData['pages']) && is_numeric($bookData['numberOfPages'])) {
                $book->setPages((int) $bookData['pages']);
            }

            $this->entityManager->persist($book);
        }

        $this->entityManager->flush();

        return new Response('Libros importados correctamente.');
    }
}
