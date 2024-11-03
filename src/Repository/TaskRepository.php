<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function getAllOrdered(): array
    {
        return $this->createQueryBuilder('t')
            ->select()
            ->orderBy('t.PresentationOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    public function findOneById($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.Id = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getMaxOrder(): ?int
    {
        return $this->createQueryBuilder('t')
            ->select('MAX(t.PresentationOrder)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getMinOrder(): ?int
    {
        return $this->createQueryBuilder('t')
            ->select('MIN(t.PresentationOrder)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getUpperTask(int $order): ?Task
    {
        $targetOrder = $order - 1;
        return $this->createQueryBuilder('t')
            ->andWhere('t.PresentationOrder = :order')
            ->setParameter('order', $targetOrder)
            ->getQuery()
            ->getOneOrNullResult();
    }



    public function create(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function delete(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }

    public function update(): void
    {
        $this->getEntityManager()->flush();
    }
}
