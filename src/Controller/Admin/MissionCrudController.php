<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\PendingFilter;
use App\Entity\Mission;
use App\Entity\Vehicle;
use App\Form\PackageType;
use App\Form\PlaceType;
use App\Form\UploadFileType;
use App\Form\UploadImageType;
use App\Repository\MissionRepository;
use App\Repository\VehicleRepository;
use App\Workflow\MissionWorkflow;
use App\Workflow\State\MissionState;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\Translation\t;

class MissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Mission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['pickupPlace.name', 'deliveryPlace.name', 'pickingVehicle.plate', 'deliveringVehicle.plate'])
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('General'),
            FormField::addColumn('col-xs-12 col-md-6'),
            FormField::addFieldset(t('Pickup')),
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('pickupPlace')
                ->setFormType(PlaceType::class)
//                ->setFormTypeOption('invalid_message', 'place.not.unique')
                ->setFormTypeOption('attr', [
                    'data-ea-autocomplete-allow-item-create' => 'true',
                    'data-ea-autocomplete-create-filter' => '^(.+)\s-\s(.*)\s\([A-Z]{2}\)$',
                ])
            ,
            AssociationField::new('pickingVehicle')->setQueryBuilder(
                fn (QueryBuilder $queryBuilder): QueryBuilder => $queryBuilder->andWhere('entity.is_available = true')
            ),
            DateTimeField::new('available_at')->setColumns(4)->hideOnIndex(),
            DateTimeField::new('pickup_at')->setColumns(4)->hideOnIndex(),

            FormField::addColumn('col-xs-12 col-md-6'),
            FormField::addFieldset(t('Delivery')),

            AssociationField::new('deliveryPlace')
                ->setFormType(PlaceType::class)
//                ->setFormTypeOption('invalid_message', 'place.not.unique')
                ->setFormTypeOption('attr', [
                    'data-ea-autocomplete-allow-item-create' => 'true',
                    'data-ea-autocomplete-create-filter' => '^(.+)\s-\s(.*)\s\([A-Z]{2}\)$',
                ])
            ,
            AssociationField::new('deliveringVehicle')
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder): QueryBuilder => $queryBuilder->andWhere('entity.is_available = true')
                ),
            DateTimeField::new('delivery_at')->setColumns(4)->hideOnIndex(),
            DateTimeField::new('delivered_before_at')->setColumns(4)->hideOnIndex(),
            BooleanField::new('express')->setColumns(4),


            FormField::addColumn('col-xs-12'),
            TextAreaField::new('note')->hideOnIndex(),
//            DateTimeField::new('scheduled_at')->onlyOnDetail(),


            FormField::addTab('Packages','fas fa-cubes'),
            CollectionField::new('packages')->setColumns(12)
                ->hideOnIndex()
                ->setEntryType(PackageType::class)
                ->addFormTheme('admin/theme/packages_theme.html.twig'),
            IntegerField::new('weight')->setDisabled()->setColumns(3)->hideOnIndex(),
            TextField::new('area')->setDisabled()->setColumns(3)->hideOnIndex(),

            FormField::addTab('Details','fas fa-list-check'),
            TextField::new('status')->setDisabled()->hideOnIndex()->setColumns(3),
            BooleanField::new('delivered')->setColumns(3)->setDisabled(),
            BooleanField::new('picked')->setColumns(3)->setDisabled(),
            DateTimeField::new('scheduled_at')->setColumns(3)->setDisabled(),

            FormField::addTab('Documents', 'fas fa-upload'),
            CollectionField::new('documents')
                ->setLabel(false)
                ->setEntryType(UploadFileType::class)
                ->hideOnIndex()
            ,

            FormField::addTab('Pictures', 'fas fa-camera')->setHelp('picture.visibility.help.message'),
            CollectionField::new('pictures')
                ->setLabel(false)
                ->setEntryType(UploadImageType::class)
                ->addJsFiles(Asset::fromEasyAdminAssetPackage('field-image.js'), Asset::fromEasyAdminAssetPackage('field-file-upload.js'))
                ->hideOnIndex()
            ,

        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(PendingFilter::new('pending', 'pending.filter.label')->setFormTypeOption('mapped',false))
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $resetMission = Action::new('resetMission','Reset')
            ->linkToCrudAction('resetMission')
            ->displayIf(static fn (Mission $mission): bool
                => $mission->getStatus() === MissionState::ASSIGNED ||
                    $mission->getStatus() === MissionState::READY
            )
            ->asWarningAction()
            ->addCssClass('btn-invisible')
            ->askConfirmation()
            ;

        return $actions
            ->add(Crud::PAGE_EDIT, $resetMission)
            ->add(Crud::PAGE_DETAIL, $resetMission);
    }

    #[AdminRoute('/reset')]
    public function resetMission(MissionWorkflow $missionWorkflow): Response
    {
        $mission = $this->getContext()->getEntity()->getInstance();

        if ($mission->getStatus() === MissionState::READY) {
            $missionWorkflow->unload($mission);
        }

        if ($mission->getStatus() === MissionState::ASSIGNED) {
            $missionWorkflow->unassign($mission);
        }

        $em = $this->container->get('doctrine')->getManager();
        $em->flush();

        return $this->redirectToRoute('admin_mission_index');
    }

    #[AdminRoute('/picking/{id}')]
    public function pickingVehicleMission(Vehicle $vehicle, Request $request): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $missionRepository = $this->container->get('doctrine')->getManager()->getRepository(Mission::class);
        $result = $missionRepository->findPickingVehicleMission($start, $end, $vehicle);

        return $this->json($result);
    }

    #[AdminRoute('/delivering/{id}')]
    public function deliveringVehicleMission(Vehicle $vehicle, Request $request): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $missionRepository = $this->container->get('doctrine')->getManager()->getRepository(Mission::class);
        $result = $missionRepository->findDeliveringVehicleMission($start, $end, $vehicle);

        return $this->json($result);
    }



    #[AdminRoute('/calendar/move/{id}')]
    public function calendarMove(Mission $mission, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $start = $request->query->get('start');
        $type = $request->query->get('type');
        $startDateTime = new \DateTimeImmutable($start);

        if ('picking' === $type) {
            if ($mission->getAvailableAt() and $mission->getAvailableAt() > new \DateTimeImmutable($start)) {
                return new Response(sprintf("Non ritirabile prima delle %s", $mission->getAvailableAt()->format('d.m H:i')), Response::HTTP_NOT_ACCEPTABLE);
            }
            $mission->setPickupAt($startDateTime);
            $mission->setScheduledAt($startDateTime);
        } else {
            $mission->setDeliveryAt($startDateTime);
            $mission->setScheduledAt($startDateTime);
        }

        $missionRepository = $this->container->get('doctrine')->getManager()->getRepository(Mission::class);
        $missionRepository->save($mission, true);

        return new Response('', Response::HTTP_OK);
    }

    #[AdminRoute('/calendar/drop/{id}')]
    public function calendarDrop(Mission $mission, Request $request, MissionWorkflow $missionWorkflow): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $vehicleId = $request->query->get('vehicleId');
        $status = $request->query->get('status');
        $start = $request->query->get('start');

        $vehicleRepository = $this->container->get('doctrine')->getManager()->getRepository(Vehicle::class);
        $vehicle = $vehicleRepository->find($vehicleId);

        $missionRepository = $this->container->get('doctrine')->getManager()->getRepository(Mission::class);
        if ($status === MissionState::START && null !== $vehicleId) {
            if ($mission->getAvailableAt() and $mission->getAvailableAt() > new \DateTimeImmutable($start)) {
                return new Response(sprintf("Non ritirabile prima delle %s", $mission->getAvailableAt()->format('d.m H:i')), Response::HTTP_NOT_ACCEPTABLE);
            }
            $mission->setPickingVehicle($vehicle);
            $mission->setPickupAt(new \DateTimeImmutable($start));
            $mission->setScheduledAt(new \DateTimeImmutable($start));
            $missionWorkflow->assign($mission);
        } else { # delivering
            $mission->setDeliveringVehicle($vehicle);
            $missionWorkflow->load($mission);
            $mission->setDeliveryAt(new \DateTimeImmutable($start));
            $mission->setScheduledAt(new \DateTimeImmutable($start));
        }

        $missionRepository->save($mission, true);

        return new Response('', Response::HTTP_OK);
    }


    #[AdminRoute('/management')]
    public function management(MissionRepository $missionRepository, VehicleRepository $vehicleRepository): Response
    {
        $missionsStart = $missionRepository->findMissionToManagedByStatus(MissionState::START);
        $missionsPending = $missionRepository->findMissionToManagedByStatus(MissionState::PENDING);

        $mappedStartMission = array_map(function ($item) {
            if ($item['express']) {
                $item['color'] = '#1c4971';
            }
            return $item;
        }, $missionsStart);

        $mappedPendingMission = array_map(function ($item) {
            $item['color'] = '#629677';
            return $item;
        }, $missionsPending);

        $vehicles = $vehicleRepository->findBy(['is_available' => true]);

        return $this->render('admin/mission/management.html.twig', [
            'start_missions' => $mappedStartMission,
            'pending_missions' => $mappedPendingMission,
            'vehicles' => $vehicles,
        ]);

    }
}
