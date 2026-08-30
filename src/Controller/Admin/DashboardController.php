<?php

namespace App\Controller\Admin;

use App\Repository\DriverLogRepository;
use App\Repository\MissionRepository;
use App\Repository\PlaceRepository;
use App\Repository\VehicleRepository;
use App\Workflow\State\MissionState;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        readonly PlaceRepository $placeRepository,
        readonly MissionRepository $missionRepository,
        readonly DriverLogRepository $driverLogRepository
    )
    {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard/dashboard.html.twig', [
            'places' => $this->placeRepository->findNullCoordinates(5),
            'mission_start' =>$this->missionRepository->findMissionToManagedByStatus(MissionState::START, 5),
            'mission_pending' => $this->missionRepository->findMissionToManagedByStatus(MissionState::PENDING, 5),
            'driver_logs' => $this->driverLogRepository->findTodayLogs(),
        ]);

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Truck26')
            ->useEntityTranslations()
            ;
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addAssetMapperEntry('admin')
            ->addCssFile('admin/style/content_print.css')
            ;
    }


    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Mission');
//        yield MenuItem::linkTo(AddressCrudController::class, null, 'fas fa-list');
        yield MenuItem::linkTo(PlaceCrudController::class, null, 'fas fa-location-dot');
        yield MenuItem::subMenu('Mission', 'fas fa-flag-checkered')
            ->setSubItems([
            MenuItem::linkToRoute('Add', 'fas fa-plus-circle', 'admin_mission_new'),
            MenuItem::linkToRoute('Management', 'fas fa-calendar', 'admin_mission_management'),
            MenuItem::linkTo(MissionCrudController::class, null, 'fas fa-list'),
        ]);
//        yield MenuItem::linkTo(PackageCrudController::class, null, 'fas fa-list');
        yield MenuItem::linkTo(VehicleCrudController::class, null, 'fas fa-truck');
        yield MenuItem::linkTo(DriverLogCrudController::class, null, 'fas fa-calendar-days');
        // yield MenuItem::linkTo(SomeCrudController::class, 'The Label', 'fas fa-list');
        yield MenuItem::section('Settings');
        yield MenuItem::linkTo(UserCrudController::class, null, 'fas fa-users');
        yield MenuItem::linkToLogout('Logout', 'fas fa-sign-out-alt');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ;
    }


}
