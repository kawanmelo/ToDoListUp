<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use PHPUnit\Exception;
use Symfony\Component\Cache\Exception\LogicException;
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

    #[Route('/home', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        $tasks = $this->taskRepository->getAllOrdered();

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
        try {
            $name = $request->get('task_name');
            $cost = $request->get('task_cost');
            $limitDate = new \DateTime($request->get('task_limit_date'));
            $presentationOrder = $this->taskRepository->getMaxOrder() + 1;
            $task = new Task();
            $task->setName($name);
            $task->setCost($cost);
            $task->setLimitDate($limitDate);

            $task->setPresentationOrder($presentationOrder);

            $errors = $validator->validate($task);
            if (count($errors) > 0) {
                throw new ValidationFailedException($task, $errors);
            }
            $this->taskRepository->create($task);
            $this->addFlash('success', 'Task created!');
            return $this->redirectToRoute('app_home');

        } catch (\DateMalformedStringException| \TypeError| ValidationFailedException $e) {
            $this->addFlash('error', "Error in data validation");
            return $this->redirectToRoute('app_create');
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash('error', 'Already exists one task with this name');
            return $this->redirectToRoute('app_create');
        }
    }

    #[Route('home/delete/{id}', name: 'app_delete')]
    public function delete(Request $request, int $id): Response
    {
        try {
            $task = $this->taskRepository->find($id);
            if (is_null($task)) {
                throw new InvalidArgumentException();
            }
            $this->taskRepository->delete($task);
            $this->addFlash('success', 'Task deleted successfully');
            return $this->redirectToRoute('app_home');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', 'Error in deleting task');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/home/edit', name: 'app_edit')]
    public function edit(Request $request, ValidatorInterface $validator): Response
    {
        try {
            $task = $this->taskRepository->find($request->get('task_id'));
            if (is_null($task)) {
                throw new InvalidArgumentException();
            }
            $task->setName($request->get('task_name'));
            $task->setCost($request->get('task_cost'));
            $task->setLimitDate(new \DateTime($request->get('task_limit_date')));
            $errors = $validator->validate($task);
            if (count($errors) === 0) {
                $this->taskRepository->update();
                $this->addFlash('success', 'Task updated successfully');
                return $this->redirectToRoute('app_home');
            }
            throw new ValidationFailedException($task, $errors);
        } catch (InvalidArgumentException | \TypeError | ValidationFailedException $e) {
            $this->addFlash('error', 'Error in edit task');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/home/moveup/{id}', name: 'app_moveup')]
    public function moveUp(int $id): Response
    {
        try{
            $task = $this->taskRepository->find($id);
            if(is_null($task)){
                throw new InvalidArgumentException('Task not found');
            }
            $minOrder = $this->taskRepository->getMinOrder();
            if($minOrder === $task->getPresentationOrder()){
                throw new \LogicException('Task is already on top');
            }
            $upperTask = $this->taskRepository->getUpperTask($task->getPresentationOrder());
            if(!is_null($upperTask)){
                $task->setPresentationOrder($task->getPresentationOrder() - 1);
                $upperTask->setPresentationOrder($upperTask->getPresentationOrder() + 1);
                $this->taskRepository->update();
                $this->addFlash('success', 'Task moved up successfully');
                return $this->redirectToRoute('app_home');
            }
            throw new LogicException('Error in moving task');
        }catch(InvalidArgumentException |\LogicException $e){
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('home/movedown/{id}', name: 'app_movedown')]
    public function moveDown(int $id): Response
    {
        try{
            $task = $this->taskRepository->find($id);
            if(is_null($task)){
                throw new InvalidArgumentException('Task not found');
            }
            $maxOrder = $this->taskRepository->getMaxOrder();
            if($maxOrder === $task->getPresentationOrder()){
                throw new \LogicException('Task is already on top');
            }
            $lowerTask = $this->taskRepository->getLowerTask($task->getPresentationOrder());
            if(!is_null($lowerTask)){
                $task->setPresentationOrder($task->getPresentationOrder() + 1);
                $lowerTask->setPresentationOrder($lowerTask->getPresentationOrder() - 1);
                $this->taskRepository->update();
                $this->addFlash('success', 'Task moved up successfully');
                return $this->redirectToRoute('app_home');
            }
            throw new LogicException('Error in moving task');
        }catch(InvalidArgumentException |\LogicException $e){
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
}
