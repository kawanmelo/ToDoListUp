<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HomeController extends AbstractController
{

    private readonly TaskRepository $taskRepository;
    public function __construct(TaskRepository $taskRepository){
        $this->taskRepository = $taskRepository;
    }
    #[Route('/home', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        $tasks = $this->taskRepository->findAll();

        return $this->render('home/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('home/create', name: 'app_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('home/create.html.twig');
    }

    #[Route('home/persist', name: 'app_persist', methods: ['POST'])]
    public function persist(Request $request, ValidatorInterface $validator): Response
    {
        $name = $request->get('task_name');
        $cost = $request->get('task_cost');
        $limitDate = new \DateTime($request->get('task_limit_date')) ;
        $presentationOrder = $this->taskRepository->getMaxOrder() + 1;
        $task = new Task();
        $task->setName($name);
        $task->setCost($cost);
        $task->setLimitDate($limitDate);

        $task->setPresentationOrder($presentationOrder);

        $errors = $validator->validate($task);
        if(count($errors) > 0){
            return $this->render('home/create.html.twig');
        }
        $this->taskRepository->create($task);
        return $this->redirectToRoute('app_home');
    }

    #[Route('home/delete/{id}', name: 'app_delete', methods: ['DELETE'],)]
    public function delete(Request $request, int $id): Response
    {

    }
}
