<?php

namespace App\Service;

use App\Entity\ServiceOperationResult;
use App\Entity\Task;
use App\Interface\TaskServiceInterface;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

class TaskService implements TaskServiceInterface
{
    private readonly TaskRepository $taskRepository;
    private readonly ValidatorInterface $validator;
    public function __construct(TaskRepository $taskRepository, ValidatorInterface $validator)
    {
        $this->taskRepository = $taskRepository;
        $this->validator = $validator;
    }

    public function createTask(string $newName, float $newCost, string $newLimitDate): ServiceOperationResult
    {
        try {
            $presentationOrder = $this->taskRepository->findMaxOrder() + 1;
            $task = new Task();
            $task->setName($newName);
            $task->setCost($newCost);
            $task->setLimitDate(new \DateTime($newLimitDate));
            $task->setPresentationOrder($presentationOrder);

            $errors = $this->validator->validate($task);
            if (count($errors) > 0) {
                throw new ValidationFailedException($task, $errors);
            }
            $this->taskRepository->create($task);
            return new ServiceOperationResult(
                Success: true,
                Message: 'Task created successfully',
            );

        } catch ( \DateMalformedStringException | \TypeError| ValidationFailedException $e) {
            return new ServiceOperationResult(
                Success: false,
                Message: 'Error in data validation',
            );
        } catch (UniqueConstraintViolationException $e) {
            return new ServiceOperationResult(
                Success: false,
                Message: 'Already exists one task with this name',
            );
        }
    }

    public function deleteTask(int $id): ServiceOperationResult
    {
        try {
            $task = $this->taskRepository->find($id);
            if (is_null($task)) {
                throw new InvalidArgumentException();
            }
            $this->taskRepository->delete($task);

            // Reset the presentation order of all tasks
            $tasks = $this->taskRepository->findAllOrdered();
            $order = 1;
            foreach ($tasks as $task) {
                $task->setPresentationOrder($order);
                $order++;
            }
            $this->taskRepository->update();

            return new ServiceOperationResult(
                Success: true,
                Message: 'Task deleted successfully',
            );
        } catch (InvalidArgumentException $e) {
            return new ServiceOperationResult(
                Success: false,
                Message: 'Error in delete task',
            );
        }
    }


    public function editTask(int $id, ?string $newName, ?float $newCost, ?string $newLimitDate): ServiceOperationResult
    {
        try {
            $task = $this->taskRepository->find($id);
            if (is_null($task)) {
                throw new InvalidArgumentException();
            }
            $task->setName($newName);
            $task->setCost($newCost);
            $task->setLimitDate(new \DateTime($newLimitDate));
            $errors = $this->validator->validate($task);
            if (count($errors) === 0) {
                $this->taskRepository->update();
                return new ServiceOperationResult(
                    Success: true,
                    Message: 'Task updated successfully',
                );
            }
            throw new ValidationFailedException($task, $errors);
        } catch ( \DateMalformedStringException | InvalidArgumentException | \TypeError | ValidationFailedException $e) {
            return new ServiceOperationResult(
                Success: false,
                Message: 'Error in edit task',
            );
        } catch (UniqueConstraintViolationException $e) {
            return new ServiceOperationResult(
                Success: false,
                Message: 'Already exists one task with this name',
            );
        }
    }

    public function moveTaskUp(int $id): ServiceOperationResult
    {
        try{
            $task = $this->taskRepository->find($id);
            if(is_null($task)){
                throw new InvalidArgumentException('Task not found');
            }
            $minOrder = $this->taskRepository->findMinOrder();
            if($minOrder === $task->getPresentationOrder()){
                throw new \LogicException('Task is already on top');
            }
            $upperTask = $this->taskRepository->findUpperTask($task->getPresentationOrder());
            if(!is_null($upperTask)){
                $task->setPresentationOrder($task->getPresentationOrder() - 1);
                $upperTask->setPresentationOrder($upperTask->getPresentationOrder() + 1);
                $this->taskRepository->update();
                return new ServiceOperationResult(
                    Success: true,
                    Message: 'Task moved up successfully',
                );
            }
            throw new LogicException('Error in moving task');
        }catch(InvalidArgumentException |\LogicException $e){
            return new ServiceOperationResult(
              Success: false,
              Message: $e->getMessage(),
            );
        }
    }

    public function moveTaskDown(int $id): ServiceOperationResult
    {
        try{
            $task = $this->taskRepository->find($id);
            if(is_null($task)){
                throw new InvalidArgumentException('Task not found');
            }
            $maxOrder = $this->taskRepository->findMaxOrder();
            if($maxOrder === $task->getPresentationOrder()){
                throw new \LogicException('Task is already on bottom');
            }
            $lowerTask = $this->taskRepository->findLowerTask($task->getPresentationOrder());
            if(!is_null($lowerTask)){
                $task->setPresentationOrder($task->getPresentationOrder() + 1);
                $lowerTask->setPresentationOrder($lowerTask->getPresentationOrder() - 1);
                $this->taskRepository->update();
                return new ServiceOperationResult(
                    Success: true,
                    Message: 'Task moved down successfully',
                );
            }
            throw new LogicException('Error in moving task');
        }catch(InvalidArgumentException |\LogicException $e){
            return new ServiceOperationResult(
                Success: false,
                Message: $e->getMessage(),
            );
        }
    }

}