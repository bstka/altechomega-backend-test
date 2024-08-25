<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use ArrayObject;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/books', name: 'app_book_')]
class BookController extends AbstractController
{
    #[Route('/', name: 'list', methods: ["GET"])]
    public function index(Request $request, BookRepository $bookRepo): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);

        $books = $bookRepo->getAll($page, $limit);

        return $this->json($books);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => $books['books'],
            'pagination' => [
                'page' => $books['page'],
                'total_page' => $books['total_page'],
            ],
        ], Response::HTTP_OK, [], ['group' => 'author:read']);
    }

    #[Route('/', name: 'insert', methods: ["POST"])]
    public function insert(Request $request, BookRepository $bookRepo, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $payload = $request->getPayload();
        $book = new Book();
        $date = DateTime::createFromFormat('Y-m-d', $payload->getString('publish_date'));

        if (!$date) {
            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'error' => [
                    "published_date" => 'Invalid date'
                ],
                'data' => new ArrayObject()
            ], Response::HTTP_BAD_REQUEST);
        }

        $author = $entityManager->find(Author::class, $payload->getInt('author_id'));

        if (!$author) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'error' => [
                    "author_id" => Response::$statusTexts[Response::HTTP_NOT_FOUND]
                ],
                'data' => new ArrayObject()
            ], Response::HTTP_NOT_FOUND);
        }

        $book->setTitle($payload->getString('title'))
            ->setDescription($payload->getString('description'))
            ->setPublishDate($date)
            ->setAuthorId($author->getId())
            ->setAuthor($author);

        $validate = $validator->validate($book);

        if (count($validate) > 0) {
            $errors = [];

            foreach ($validate as $key => $value) {
                $errors[$value->getPropertyPath()] = $value->getMessage();
            }

            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'error' => $errors,
                'data' => new ArrayObject()
            ], Response::HTTP_BAD_REQUEST);
        }

        $bookRepo->save($book, true);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => [
                'id' => $book->getId()
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update', methods: ["PUT"])]
    public function update(Book $book, Request $request, BookRepository $bookRepo, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $payload = $request->getPayload();
        $date = DateTime::createFromFormat('Y-m-d', $payload->getString('publish_date'));

        if (!$book) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'data' => new ArrayObject()
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$date) {
            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'error' => [
                    "published_date" => 'Invalid date'
                ],
                'data' => new ArrayObject()
            ], Response::HTTP_BAD_REQUEST);
        }

        $author = $entityManager->find(Author::class, $payload->getInt('author_id'));

        if (!$author) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'error' => [
                    "author_id" => Response::$statusTexts[Response::HTTP_NOT_FOUND]
                ],
                'data' => new ArrayObject()
            ], Response::HTTP_NOT_FOUND);
        }

        $book->setTitle($payload->getString('title'))
            ->setDescription($payload->getString('description'))
            ->setPublishDate($date)
            ->setAuthor($author);

        $validate = $validator->validate($book);

        if (count($validate) > 0) {
            $errors = [];

            foreach ($validate as $key => $value) {
                $errors[$value->getPropertyPath()] = $value->getMessage();
            }

            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'error' => $errors,
                'data' => new ArrayObject()
            ], Response::HTTP_BAD_REQUEST);
        }

        $bookRepo->save($book, true);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => [
                'id' => $book->getId()
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'view', methods: ["GET"])]
    public function view(Book $book): JsonResponse
    {
        if (!$book) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'data' => new ArrayObject()
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => $book
        ], Response::HTTP_OK, [], ['groups' => ['book:basic']]);
    }

    #[Route('/{id}', name: 'delete', methods: ["DELETE"])]
    public function delete(Book $book, BookRepository $bookRepo): JsonResponse
    {
        if (!$book) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'data' => new ArrayObject()
            ], Response::HTTP_BAD_REQUEST);
        }

        $bookRepo->delete($book, true);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => new ArrayObject()
        ], Response::HTTP_OK);
    }
}
