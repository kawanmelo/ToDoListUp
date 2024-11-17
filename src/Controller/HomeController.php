<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HomeController extends AbstractController
{

    private readonly TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    // temporally redirect
    #[Route('/', name: 'redirect_home')]
    public function redirectToHome(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/home', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        $tasks = $this->taskRepository->findAllOrdered();

        return $this->render('home/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('home/count/{direction<up|down>}', name: 'app_count', methods: ['GET'])]
    public function count(string $direction): JsonResponse
    {
        if($direction === 'up'){
            $num = random_int(40, 100);
        }
        else{
            $num = random_int(1, 40);
        }
        return $this->json(['num' => $num]);
    }
}
