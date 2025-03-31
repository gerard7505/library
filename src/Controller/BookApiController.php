<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DateTime;

class BookApiController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Obtener todos los libros
    #[Route('/api/books', name: 'api_books', methods: ['GET'])]
    public function getAllBooks(): JsonResponse
    {
        $books = $this->entityManager->getRepository(Book::class)->findAll();
        return $this->json($books);
    }

    // Obtener los libros publicados antes de 2013
    #[Route('/api/books/before-2013', name: 'api_books_before_2013', methods: ['GET'])]
    public function getBooksBefore2013(): JsonResponse
    {
        $books = $this->entityManager->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->where('b.published < :year')
            ->setParameter('year', new \DateTime('2013-01-01'))
            ->getQuery()
            ->getResult();

        return $this->json($books);
    }

    // Obtener los libros de la categoría "Drama"
    #[Route('/api/books/category/drama', name: 'api_books_drama', methods: ['GET'])]
    public function getBooksDrama(): JsonResponse
    {
        $books = $this->entityManager->getRepository(Book::class)
            ->findBy(['category' => 'Drama']);

        return $this->json($books);
    }

    // Actualizar un libro por ISBN
    #[Route('/api/book/{isbn}', name: 'api_book_by_isbn', methods: ['POST'])]
    public function updateBook(string $isbn, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $book = $this->entityManager->getRepository(Book::class)->findOneBy(['isbn' => $isbn]);
        
        if (!$book) {
            return $this->json(['error' => 'Book not found'], 404);
        }

        $book->setTitle($data['title'] ?? $book->getTitle());
        $book->setAuthor($data['author'] ?? $book->getAuthor());
        $book->setPublished(new \DateTime($data['published'] ?? $book->getPublished()->format('Y-m-d')));
        $book->setPublisher($data['publisher'] ?? $book->getPublisher());
        $book->setDescription($data['description'] ?? $book->getDescription());
        $book->setWebsite($data['website'] ?? $book->getWebsite());
        $book->setPages($data['pages'] ?? $book->getPages());
        $book->setCategory($data['category'] ?? $book->getCategory());

        if (!empty($data['images'])) {
            $book->setImages($data['images']);
        }

        $this->entityManager->flush();

        return $this->json([
            'isbn' => $book->getIsbn(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'publisher' => $book->getPublisher(),
            'published' => $book->getPublished()->format('Y-m-d'),
            'subtitle' => $book->getSubtitle(),
            'description' => $book->getDescription(),
            'website' => $book->getWebsite(),
            'pages' => $book->getPages(),
            'category' => $book->getCategory(),
            'images' => $book->getImages() 
        ]);
    }

    // Añadir un libro
    #[Route('/api/book', name: 'api_add_book', methods: ['POST'])]
    public function addBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['isbn']) || empty($data['title'])) {
            return $this->json(['error' => 'ISBN and title are required'], 400);
        }

        $publishedDate = $data['published'] ?? null;
        if ($publishedDate && !\DateTime::createFromFormat('Y-m-d', $publishedDate)) {
            return $this->json(['error' => 'Invalid date format for published, use YYYY-MM-DD'], 400);
        }

        $book = new Book();
        $book->setIsbn($data['isbn']);
        $book->setTitle($data['title']);
        $book->setAuthor($data['author'] ?? null);
        $book->setPublisher($data['publisher'] ?? null);
        $book->setSubtitle($data['subtitle'] ?? null);
        $book->setDescription($data['description'] ?? null);
        $book->setWebsite($data['website'] ?? null);
        $book->setPages($data['pages'] ?? null);
        $book->setCategory($data['category'] ?? null);

        if ($publishedDate) {
            $book->setPublished(new \DateTime($publishedDate));
        }

        $book->setImages($data['images'] ?? []);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json(['message' => 'Book added successfully', 'book' => $book], 201);
    }

    // Borrar un libro por ISBN
    #[Route('/api/book/{isbn}', name: 'api_delete_book', methods: ['DELETE'])]
    public function deleteBook(string $isbn): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->findOneBy(['isbn' => $isbn]);

        if (!$book) {
            return $this->json(['error' => 'Book not found'], 404);
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return $this->json(['message' => 'Book deleted successfully'], 200);
    }

    // Obtener libros con información de reservas
    #[Route('/api/books/with-reservations', name: 'api_books_with_reservations', methods: ['GET'])]
    public function getBooksWithReservations(): JsonResponse
    {
        $books = $this->entityManager->getRepository(Book::class)->findAll();
        
        $booksWithReservations = [];
        foreach ($books as $book) {
            $reservation = $this->entityManager->getRepository(Reservation::class)
                ->createQueryBuilder('r')
                ->where('r.book = :book')
                ->andWhere('r.endDate >= :now') 
                ->setParameter('book', $book)
                ->setParameter('now', new \DateTime())
                ->getQuery()
                ->getOneOrNullResult();

            $bookData = [
                'isbn' => $book->getIsbn(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'category' => $book->getCategory(),
            ];

            if ($reservation) {
                $bookData['reservation'] = [
                    'id' => $reservation->getId(),  
                    'userEmail' => $reservation->getUserEmail(),
                    'startDate' => $reservation->getStartDate()->format('Y-m-d H:i:s'),
                    'endDate' => $reservation->getEndDate()->format('Y-m-d H:i:s'),
                ];
            }

            $booksWithReservations[] = $bookData;
        }

        return $this->json($booksWithReservations);
    }

    // Obtener todas las reservas
    #[Route('/api/reservations', name: 'api_reservations', methods: ['GET'])]
    public function getAllReservations(): JsonResponse
    {
        $reservations = $this->entityManager->getRepository(Reservation::class)->findAll();
        return $this->json($reservations);
    }

    // Borrar una reserva por ID
    #[Route('/api/reservation/{id}', name: 'api_delete_reservation', methods: ['DELETE'])]
    public function deleteReservation(int $id): JsonResponse
    {
        $reservation = $this->entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], 404);
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return $this->json(['message' => 'Reservation deleted successfully'], 200);
    }

    // Reservar un libro por ISBN
    #[Route('/api/book/{isbn}/reserve', name: 'api_reserve_book', methods: ['POST'])]
    public function reserveBook(string $isbn, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        if (!isset($data['userEmail'], $data['startDate'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        if (!filter_var($data['userEmail'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Invalid email format'], 400);
        }

        // Verificar si el usuario ya tiene una reserva activa
        $existingReservation = $this->entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->where('r.userEmail = :userEmail')
            ->andWhere('r.endDate >= :now') 
            ->setParameter('userEmail', $data['userEmail'])
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingReservation) {
            return $this->json(['error' => 'You already have an active reservation'], 400);
        }

        // Validar el formato de la fecha de inicio
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['startDate']);
        if (!$startDate) {
            return $this->json(['error' => 'Invalid start date format, expected Y-m-d H:i:s'], 400);
        }

        // Calcular la fecha de finalización (30 días después)
        $endDate = clone $startDate;
        $endDate->modify('+30 days');

        // Buscar el libro por ISBN
        $book = $this->entityManager->getRepository(Book::class)->findOneBy(['isbn' => $isbn]);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], 404);
        }

        // Comprobar si el libro ya tiene una reserva activa
        $existingBookReservation = $this->entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->where('r.book = :book')
            ->andWhere('r.endDate >= :now')
            ->setParameter('book', $book)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingBookReservation) {
            return $this->json(['error' => 'This book is already reserved'], 400);
        }

        // Crear la nueva reserva
        $reservation = new Reservation();
        $reservation->setBook($book);
        $reservation->setUserEmail($data['userEmail']);
        $reservation->setStartDate($startDate);
        $reservation->setEndDate($endDate);

        // Persistir la nueva reserva
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Book reserved successfully',
            'reservation' => [
                'user_email' => $reservation->getUserEmail(),
                'start_date' => $startDate->format('Y-m-d H:i:s'),
                'end_date' => $endDate->format('Y-m-d H:i:s')
            ]
        ], 200);
    }
}
