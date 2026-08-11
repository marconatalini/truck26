<?php

namespace App\Repository;

use App\Entity\DriverLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<DriverLog>
 */
class DriverLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DriverLog::class);
    }

    /**
     * @return DriverLog[] Returns an array of DriverLog objects
     */
    public function findCurrentDriverVehicle(UserInterface $driver): DriverLog|null
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.driver = :driver')
            ->andWhere('d.startDate <= :today')
            ->andWhere('d.endDate >= :today')
            ->setParameter('driver', $driver)
            ->setParameter('today', new \DateTime('now'))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findCalendarDriverLogs(string $startDate, string $endDate, UserInterface $user): ?array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.vehicle', 'v')
            ->select('d.id','d.startDate as start', 'd.endDate as end', 'd.category as title')
            ->where('d.startDate >= :start AND d.endDate <= :end')
            ->andWhere('d.driver = :user')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
    }

    public function findOverlapsLog(DriverLog $driverLog): ?DriverLog
    {

        $qb = $this->createQueryBuilder('d')
            ->andWhere('d.driver = :driver')
            ->andWhere('d.category = :category')
            ->andWhere('d.startDate <= :end AND d.endDate >= :start')
            ->setParameter('driver', $driverLog->getDriver())
            ->setParameter('category', $driverLog->getCategory())
            ->setParameter('start', $driverLog->getStartDate())
            ->setParameter('end', $driverLog->getEndDate())
        ;

        if (null !== $driverLog->getId()) {
            // prevent find the same Logs on update
            $qb->andWhere('d.id != :id')
                ->setParameter('id', $driverLog->getId())
            ;
        }

        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function findTodayLogs(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.startDate <= :today')
            ->andWhere('d.endDate >= :today')
            ->setParameter('today', new \DateTime('now'))
            ->getQuery()
            ->getResult()
            ;
    }
}
