<?php

namespace App\Controller\Admin;

use App\Entity\Mission;
use App\Entity\Vehicle;
use App\Repository\MissionRepository;
use App\Service\PdfService;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class VehicleCrudController extends AbstractCrudController
{
    const CHART_COLORS = [
        "red" => 'rgb(255, 99, 132)',
        "light_red" => 'rgba(255, 99, 132, 0.4)',
        "orange" => 'rgb(255, 159, 64)',
        "yellow" => 'rgb(255, 205, 86)',
        "green" => 'rgb(75, 192, 192)',
        "blue" => 'rgb(54, 162, 235)',
        "light_blue" => 'rgba(54, 162, 235, 0.4)',
        "purple" => 'rgb(153, 102, 255)',
        "grey" => 'rgb(201, 203, 207)'
    ];

    public static function getEntityFqcn(): string
    {
        return Vehicle::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('model')->setColumns(4),
            TextField::new('plate')->setColumns(3),
            BooleanField::new('is_available')->setColumns(2),
            ColorField::new('color')->setColumns(3),
            IntegerField::new('length')->setColumns(3)->setHelp('millimetri'),
            IntegerField::new('width')->setColumns(3)->setHelp('millimetri'),
            IntegerField::new('height')->setColumns(3)->setHelp('millimetri'),
            IntegerField::new('weight_range')->setColumns(3)->setHelp('kilogrammi'),
            DateField::new('purchase_at')->hideOnIndex()->setColumns(3),
            DateField::new('next_tax_at')->hideOnIndex()->setColumns(3),
            DateField::new('next_inspection_at')->hideOnIndex()->setColumns(3),
            DateField::new('next_tachograph_at')->hideOnIndex()->setColumns(3),
            TextAreaField::new('note'),

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $vehicleLastPlace = Action::new('localization', 'localization')
            ->createAsGlobalAction()
            ->linkToCrudAction('showVehicle');

        $multiPicking = Action::new('multiPicking', 'multiPick', 'fa-solid fa-layer-group')
            ->linkToRoute('admin_multi_mission_new',
                fn (Vehicle $entity) => ['vehicleId' => $entity->getId()
            ])
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $vehicleLastPlace)
            ->add(Crud::PAGE_INDEX, $multiPicking)
            ;
    }

    #[AdminRoute('/localization')]
    public function showVehicle(MissionRepository $missionRepository): Response
    {
        return $this->render('admin/vehicle/vehicles_maps.html.twig', [
            'result' => $missionRepository->findLastVehiclePlace(),
        ]);
    }

    #[AdminRoute('/map/{id}', name: 'map')]
    public function map(Vehicle $vehicle, MissionRepository $missionRepository): Response
    {

        $missions = $missionRepository->findAllVehicleMissionCoords($vehicle);

        return $this->render('admin/vehicle/map.html.twig', [
            'vehicle' => $vehicle,
            'missions' => $missions,
        ]);
    }

    #[AdminRoute('/pdf/{id}', name: 'pdf')]
    public function generaPdf(Vehicle $vehicle, PdfService $pdfService, MissionRepository $missionRepository): Response
    {
        $missions = $missionRepository->findAllOpenMissionsByVehicle($vehicle);
        $today = new DateTime('now');

        // 2. Renderizza il template Twig in HTML
        $html = $this->renderView('admin/vehicle/pdf.html.twig', [
            'vehicle' => $vehicle,
            'missions' => $missions,
            'today' => $today
        ]);

        /*return $this->render('mission/pdf/index.html.twig', [
            'vehicle' => $vehicle,
            'missions' => $missions,
            'today' => $today
        ]);*/

        return $pdfService->pdfResponse($html,
            sprintf("Missioni %s del %s", $vehicle->getPlate(), date_format($today,'d-m-Y')));
    }

    #[AdminRoute('/chart/{id}', name: 'chart')]
    public function chart(Vehicle $vehicle, ChartBuilderInterface $chartBuilder, MissionRepository $missionRepository): Response
    {

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $missions = $missionRepository->findAllOpenMissionsByVehicle($vehicle);
        $loaded = $missionRepository->findWeightAndAreaLoadedByVehicle($vehicle);

        if ([] === $missions) {
            return $this->render('admin/vehicle/chart.html.twig', [
                'vehicle' => $vehicle,
            ]);
        }

        $labels = ['Attuale'];
        $mq = $loaded[0]['total_area']*100;
        $mqData = [$mq];
        $kg = $loaded[0]['total_weight'];
        $kgData = [$kg];
        $maxKg = $vehicle->getWeightRange();
        $maxMq = $vehicle->getWidth() * $vehicle->getLength()/10000;
        $maxMqData = [$maxMq];
        $maxKgData = [$maxKg];
        $restMqData = [$maxMq];
        $restKgData = [$maxKg];


        /** @var Mission $mission */
        foreach ($missions as $mission) {
            if (false === $mission->isPicked()) { //carico
                $labels[] = $mission->getPickupPlace()->getName();
                $kg += $mission->getWeight();
                $mq += $mission->getArea()*100;
                $kgData[] = $kg;
                $mqData[] = $mq;
                $restKgData[] = max($maxKg - $kg, 0);
                $restMqData[] = max($maxMq - $mq, 0);
            } else {
                $labels[] = $mission->getDeliveryPlace()->getName();
                $kg -= $mission->getWeight();
                $mq -= $mission->getArea()*100;
                $kgData[] = $kg;
                $mqData[] = $mq;
                $restKgData[] = max($maxKg + $kg, 0);
                $restMqData[] = max($maxMq + $mq, 0);
            }
            $maxMqData[] = $maxMq;
            $maxKgData[] = $maxKg;
        }


        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Spazio',
                    'backgroundColor' => self::CHART_COLORS['red'],
                    'data' => $mqData,
                ],
                [
//                    'label' => 'Rest',
                    'backgroundColor' => self::CHART_COLORS['light_red'],
                    'data' => $restMqData,
                ],
                [
                    'label' => 'Peso',
                    'backgroundColor' => self::CHART_COLORS['blue'],
                    'data' => $kgData,
                ],
                [
//                    'label' => 'Rest',
                    'backgroundColor' => self::CHART_COLORS['light_blue'],
                    'data' => $restKgData,
                ],

            ],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false
                ],
            ],
            'scales' => [
                'y' => [
//                    'drawBorder' => true,
//                    'color' => 'red',
                    'stacked' => true
                ],
                'x' => [
                    'stacked' => true
                ]
            ]
        ]);


        return $this->render('admin/vehicle/chart.html.twig', [
            'vehicle' => $vehicle,
            'chart' => $chart,
        ]);
    }

}
