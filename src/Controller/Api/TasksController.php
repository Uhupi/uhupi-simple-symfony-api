<?php

namespace App\Controller\Api;

use App\Entity\Tasks;
use App\Repository\TasksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks')]
final class TasksController extends AbstractController
{
    const HTTP_MESSAGE_NOT_FOUND = 'Sorry, task not found :(';

    #[Route('', methods: ['GET'])]
    public function index(Request $request, TasksRepository $tasksRepository): JsonResponse
    {
        $status     = $request->query->getString('status', '');
        $criteria   = [];

        if ($status) {
            $criteria['status'] = $status;
        }

        if ($request->query->getInt('page') || $request->query->getInt('limit')) {
            $page   = $request->query->getInt('page', 1);
            $limit  = $request->query->getInt('limit', 10);
            $offset = ($page - 1) * $limit;
            $tasks  = $tasksRepository->findBy($criteria, null, $limit, $offset);
            return $this->json($tasks);
        }

        return $this->json(count($criteria) ? $tasksRepository->findBy($criteria) : $tasksRepository->findAll());
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $task = new Tasks();
        $task
            ->setTitle($data['title'] ?? '')
            ->setDescription($data['description'] ?? '')
            ->setStatus($data['status'] ?? $task->getStatus())
            ->setDueDate(!empty($data['due_date']) ? new \DateTimeImmutable($data['due_date']) : null)
        ;

        $errors = $validator->validate($task);
        if (count($errors)) {
            return $this->validationErrorResponse($errors);
        }
        
        $entityManager->persist($task);
        $entityManager->flush();

        return $this->json($task, JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(?Tasks $task): JsonResponse
    {
        if (!$task) {
            return $this->errorResponse(JsonResponse::HTTP_NOT_FOUND, [self::HTTP_MESSAGE_NOT_FOUND]);
        }
        return $this->json($task);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?Tasks $task, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        if (!$task) {
            return $this->errorResponse(JsonResponse::HTTP_NOT_FOUND, [self::HTTP_MESSAGE_NOT_FOUND]);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }

        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }

        if (isset($data['due_date'])) {
            $task->setDueDate(new \DateTime($data['due_date']));
        }

        $errors = $validator->validate($task);
        if (count($errors)) {
            return $this->validationErrorResponse($errors);
        }

        $entityManager->flush();

        return $this->json($task);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(?Tasks $task, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$task) {
            return $this->errorResponse(JsonResponse::HTTP_NOT_FOUND, [self::HTTP_MESSAGE_NOT_FOUND]);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Define some standard error response
     */
    private function errorResponse(int $httpStatusCode, array $messages) : JsonResponse
    {
        return $this->json(
            [
                'error'     => true,
                'code'      => $httpStatusCode,
                'message'   => $messages,
            ], $httpStatusCode)
        ;
    }

    private function validationErrorResponse($errors): JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }
        return $this->errorResponse(JsonResponse::HTTP_BAD_REQUEST, $errorMessages);
    }
}
