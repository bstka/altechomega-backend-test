<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use ArrayObject;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/authors', name: 'app_author_')]
class AuthorController extends AbstractController
{
    #[Route('/', name: 'list', methods: ["GET"])]
    public function index(Request $request, AuthorRepository $authorRepo): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $page = $request->query->getInt('page', 1);

        $authors = $authorRepo->getAll($page, $limit);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => $authors['authors'],
            'pagination' => [
                'page' => $authors['page'],
                'total_page' => $authors['total_page'],
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/', name: 'insert', methods: ["POST"])]
    public function insert(Request $request, AuthorRepository $authorRepo, ValidatorInterface $validator): JsonResponse
    {
        $payload = $request->getPayload();
        $author = new Author();
        $date = DateTime::createFromFormat('Y-m-d', $payload->getString('birth_date'));

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

        $author->setName($payload->getString('name'))
            ->setBio($payload->getString('bio'))
            ->setBirthDate($date);

        $validate = $validator->validate($author);

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

        $authorRepo->save($author, true);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => [
                'id' => $author->getId()
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update', methods: ["PUT"])]
    public function update(Author $author, Request $request, AuthorRepository $authorRepo, ValidatorInterface $validator): JsonResponse
    {
        $payload = $request->getPayload();
        $date = DateTime::createFromFormat('Y-m-d', $payload->getString('birth_date'));

        if (!$author) {
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

        $author->setName($payload->getString('name'))
            ->setBio($payload->getString('bio'))
            ->setBirthDate($date);

        $validate = $validator->validate($author);

        if (count($validate) > 0) {
            $errors = [];

            foreach ($validate as $key => $value) {
                $errors[$value->getPropertyPath()] = $value->getMessage();
            }

            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                'error' => [
                    "published_date" => $errors
                ],
                'data' => new ArrayObject()
            ], Response::HTTP_BAD_REQUEST);
        }

        $authorRepo->save($author, true);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => [
                'id' => $author->getId()
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'view', methods: ["GET"])]
    public function view(Author $author): JsonResponse
    {
        if (!$author) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'data' => new ArrayObject()
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => $author
        ], Response::HTTP_OK);
    }

    #[Route('/{id}/books', name: 'view_books', methods: ["GET"])]
    public function viewBooks(Author $author, SerializerInterface $serializer): JsonResponse
    {
        if (!$author) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'data' => new ArrayObject()
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => $author->getBooks()
        ], Response::HTTP_OK, [], ['groups' => 'book:basic']);
    }

    #[Route('/{id}', name: 'delete', methods: ["DELETE"])]
    public function delete(Author $author, AuthorRepository $authorRepo): JsonResponse
    {
        if (!$author) {
            return $this->json([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
                'data' => new ArrayObject()
            ], Response::HTTP_NOT_FOUND);
        }

        $authorRepo->delete($author, true);

        return $this->json([
            'code' => Response::HTTP_OK,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data' => new ArrayObject()
        ], Response::HTTP_OK);
    }
}
