<?php

namespace App\Controller;

use App\Entity\ServiceOperationResult;
use App\Entity\Task;
use App\Interface\TaskServiceInterface;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use InvalidArgumentException;
use LogicException;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class TaskController extends AbstractController
{

    private readonly TaskServiceInterface $taskService;

    public function __construct(TaskRepository $taskRepository, TaskServiceInterface $taskService)
    {
        $this->taskService = $taskService;
    }
    #[Route('/task', name: 'app_task')]
    public function index(): Response
    {
        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
        ]);
    }

    #[Route('task/create', name: 'app_task_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $result = $this->taskService->createTask(
            $request->get('task_name'),
            $request->get('task_cost'),
            $request->get('task_limit_date')
        );
        if($result->isSuccess()) {
            $this->addFlash('success', $result->getMessage());
            return $this->redirectToRoute('app_home');
        }
        $this->addFlash('error', $result->getMessage());
        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/delete/{id<\d+>}', name: 'app_task_delete')]
    public function delete(int $id): Response
    {
        $result = $this->taskService->deleteTask($id);
        if($result->isSuccess()) {
            $this->addFlash('success', $result->getMessage());
            return $this->redirectToRoute('app_home');
        }
        $this->addFlash('error', $result->getMessage());
        return $this->redirectToRoute('app_home');
    }

    #[Route('/task/edit/', name: 'app_task_edit')]
    public function edit(Request $request, ValidatorInterface $validator): Response
    {
        $result = $this->taskService->editTask(
            $request->get('task_id'),
            $request->get('task_name'),
            $request->get('task_cost'),
            $request->get('task_limit_date')
        );
        if($result->isSuccess()) {
            $this->addFlash('success', $result->getMessage());
            return $this->redirectToRoute('app_home');
        }
        $this->addFlash('error', $result->getMessage());
        return $this->redirectToRoute('app_home');
    }

    #[Route('/task/moveup/{id<\d+>}', name: 'app_task_moveup')]
    public function moveUp(int $id): Response
    {
        $result = $this->taskService->moveTaskUp($id);
        if($result->isSuccess())
        {
            $this->addFlash('success', $result->getMessage());
            return $this->redirectToRoute('app_home');
        }
        $this->addFlash('error', $result->getMessage());
        return $this->redirectToRoute('app_home');
    }

    #[Route('task/movedown/{id<\d+>}', name: 'app_task_movedown')]
    public function moveDown(int $id): Response
    {
        $result = $this->taskService->moveTaskDown($id);
        if($result->isSuccess())
        {
            $this->addFlash('success', $result->getMessage());
            return $this->redirectToRoute('app_home');
        }
        $this->addFlash('error', $result->getMessage());
        return $this->redirectToRoute('app_home');
    }
}
