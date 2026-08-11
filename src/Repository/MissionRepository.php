<?php

namespace App\Repository;

use App\Entity\Mission;
use App\Entity\Vehicle;
use App\Workflow\State\MissionState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends ServiceEntityRepository<Mission>
 *
 * @method Mission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mission[]    findAll()
 * @method Mission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        readonly AdminUrlGenerator $adminUrlGenerator,
        readonly UrlGeneratorInterface $urlGenerator
    )
    {
        parent::__construct($registry, Mission::class);
    }

    public function save(Mission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Mission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOpenMissionsByVehicle(Vehicle $vehicle): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.pickingVehicle = :vehicle AND m.picked = false')
            ->orWhere('m.deliveringVehicle = :vehicle AND m.delivered = false')
            ->setParameter('vehicle', $vehicle)
            ->orderBy('m.scheduled_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllDoneMissionsByVehicle(Vehicle $vehicle): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.pickingVehicle = :vehicle AND m.picked = true')
            ->orWhere('m.deliveringVehicle = :vehicle AND m.delivered = true')
            ->andWhere('m.scheduled_at > :from_date')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('from_date', new \DateTime('-36 hours'))
            ->orderBy('m.scheduled_at', 'DESC')
            ->getQuery()
            ->getResult();
    }



    public function findWeightAndAreaLoadedByVehicle(Vehicle $vehicle): array
    {
        return $this->createQueryBuilder('m')
            ->select('SUM(m.weight) as total_weight', 'SUM(m.area) as total_area')
            ->andWhere('m.pickingVehicle = :vehicle AND m.picked = true')
            ->andWhere('m.deliveringVehicle = :vehicle AND m.delivered = false')
            ->setParameter('vehicle', $vehicle)
            ->getQuery()
            ->getScalarResult();
    }

    public function findAllVehicleMissionCoords(Vehicle $vehicle): array
    {
        $sql = "
        (select p.name, v.plate, adr.coordinates, m.scheduled_at from mission m
            left join vehicle v on m.picking_vehicle_id = v.id
            left join place p on m.pickup_place_id = p.id
            left join address adr on p.address_id = adr.id
        where (m.picking_vehicle_id = :vehicle and m.picked = false))
        union all
        (select p.name, v.plate, adr.coordinates, m.scheduled_at from mission m
            left join vehicle v on m.delivering_vehicle_id = v.id
            left join place p on m.delivery_place_id = p.id
            left join address adr on p.address_id = adr.id
         where (m.delivering_vehicle_id = :vehicle and m.delivered = false))
          order by scheduled_at ASC;
        ";

        $params = [
            'vehicle' => $vehicle->getId(),
        ];

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql, $params);
        return $result->fetchAllAssociative();
    }

    public function findPickingVehicleMission(string $start, string $end, Vehicle $vehicle): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftjoin('m.pickupPlace', 'pp')
            ->leftjoin('m.deliveryPlace', 'dp')
            ->leftjoin('m.pictures', 'pic')
            ->select('m.id','m.pickup_at as start','m.weight','m.status','m.picked','m.area','m.express',
                'pp.name as pickupPlaceName', 'dp.name as deliveryPlaceName', "'picking' as type",
            )
            ->where('m.pickup_at >= :start AND m.pickup_at <= :end')
            ->andWhere('m.pickingVehicle = :vehicle')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('vehicle', $vehicle)
            ;

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

    }

    public function findDeliveringVehicleMission(string $start, string $end, Vehicle $vehicle): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftjoin('m.pickupPlace', 'pp')
            ->leftjoin('m.deliveryPlace', 'dp')
            ->select('m.id','m.delivery_at as start','m.weight','m.status','m.delivered','m.area','m.express',
                'pp.name as pickupPlaceName', 'dp.name as deliveryPlaceName', "'delivering' as type")
            ->where('m.delivery_at >= :start AND m.delivery_at <= :end')
            ->andWhere('m.deliveringVehicle = :vehicle')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('vehicle', $vehicle)
            ;

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

    }

    public function findMissionToManagedByStatus(string $status, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.pickingVehicle', 'pv')
            ->leftjoin('m.pickupPlace', 'pp')
            ->leftjoin('m.deliveryPlace', 'dp')
            ->select('m.id','m.weight','m.status', 'm.area', 'm.express',
                'pp.name as pickupPlaceName', 'dp.name as deliveryPlaceName',
                'pv.plate',
                'CONCAT(pp.name, \' < \', m.weight, \' KG \', dp.name) as title'
            )
            ->where('m.status = :status')
            ->orderBy('m.weight', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('status', $status)
            ;

        return $qb->getQuery()->getResult();

    }

    public function findLastVehiclePlace(): array|false
    {
        $sql = "
        (select distinct on (m.picking_vehicle_id)
     p.name, v.plate, m.pickup_at as last, adr.coordinates
 from mission m
     left join place p on m.pickup_place_id = p.id
     left join vehicle v on m.picking_vehicle_id = v.id
     left join address adr on p.address_id = adr.id
 where m.picked = true
 order by m.picking_vehicle_id, m.pickup_at DESC)
union all
(select distinct on (m.delivering_vehicle_id)
     p.name, v.plate, m.delivery_at as last, adr.coordinates
 from mission m
     left join place p on m.delivery_place_id = p.id
     left join vehicle v on m.delivering_vehicle_id = v.id
     left join address adr on p.address_id = adr.id
 where m.delivered = true
 order by m.delivering_vehicle_id, m.delivery_at DESC)
order by plate, last DESC;
";

        $result = $this->getEntityManager()->getConnection()->executeQuery($sql);
        return $result->fetchAllAssociative();
    }

}
