<?php

namespace App\Interface;

use App\Entity\Task;
use DateTime;

interface TaskServiceInterface{

    public function createTask(string $newName, float $newCost, string $newLimitDate);
    public function editTask(int $id, ?string $newName, ?float $newCost, ?string $newLimitDate);
    public function deleteTask(int $id);
    public function moveTaskUp(int $id);
    public function moveTaskDown(int $id);

}
