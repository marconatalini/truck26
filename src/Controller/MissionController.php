<?php

namespace App\Controller;

use App\Entity\DriverLog;
use App\Entity\Mission;
use App\Entity\PictureUpload;
use App\Entity\Vehicle;
use App\Form\UploadImageType;
use App\Repository\DriverLogRepository;
use App\Repository\MissionRepository;
use App\Service\PdfService;
use App\Workflow\MissionWorkflow;
use App\Workflow\State\MissionState;
use DateTime;
use DateTimeImmutable;
use Dompdf\Options;
use phpDocumentor\Reflection\PseudoTypes\NonFalsyString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/mission')]
final class MissionController extends AbstractController
{
    public function __construct(
        readonly private MissionWorkflow $missionWorkflow,
        readonly private MissionRepository $missionRepository,
        readonly private DriverLogRepository $driverLogRepository
    )
    {
    }

    private function getDriverLog(): ?DriverLog
    {
        /** @var DriverLog $logs */
        return $this->driverLogRepository->findCurrentDriverVehicle($this->getUser());
    }

    #[Route('/', name: 'app_mission_index')]
    public function index(): Response
    {
        /** @var DriverLog $logs */
        $logs = $this->getDriverLog();
        if (null === $logs) {
            return $this->render('mission/sorry.html.twig', [
                'message' => 'Chiama la centrale per farti assegnare un mezzo!'
            ]);
        }

        $vehicle = $logs->getVehicle();
        $missions = $this->missionRepository->findAllOpenMissionsByVehicle($vehicle);

        return $this->render('mission/index.html.twig', [
            'vehicle' => $vehicle,
            'missions' => $missions,
        ]);
    }

    #[Route('/done', name: 'app_done_mission_index')]
    public function done(): Response
    {
        /** @var DriverLog $logs */
        $logs = $this->getDriverLog();
        if (null === $logs) {
            return $this->render('mission/sorry.html.twig', [
                'message' => 'Non ho trovato missioni da te eseguito su questo mezzo!'
            ]);
        }

        $vehicle = $logs->getVehicle();
        $missions = $this->missionRepository->findAllDoneMissionsByVehicle($vehicle);

        //workaround to prevent workflow action
        array_map(fn($mission) => $mission->setStatus(MissionState::DELIVERED), $missions);

        return $this->render('mission/index.html.twig', [
            'vehicle' => $vehicle,
            'missions' => $missions,
        ]);
    }

    #[Route('/unassigned', name: 'app_mission_unassigned_index')]
    public function unassigned(): Response
    {
        $missions = $this->missionRepository->findBy(['status' => MissionState::START]);

        return $this->render('mission/index.html.twig', [
            'vehicle' => 'Non definito',
            'missions' => $missions,
        ]);
    }



    #[Route('/{id}', name: 'app_mission_detail')]
    public function detail(Mission $mission): Response
    {
        return $this->render('mission/detail.html.twig', [
            'mission' => $mission,
        ]);
    }

    #[Route('/pickup/{id}', name: 'app_mission_pickup')]
    public function pickup(Mission $mission): Response
    {
        $this->missionWorkflow->pickup($mission);
        $this->missionRepository->save($mission, true);

        return $this->redirectToRoute('app_mission_picked', [
            'id' => $mission->getId(),
        ]);
    }

    #[Route('/picked/{id}', name: 'app_mission_picked')]
    public function picked(Mission $mission): Response
    {
        if (true === $mission->isExpress()) {
            return $this->redirectToRoute('app_mission_load_express', [
                'id' => $mission->getId(),
                'when' => 'express'
            ]);
        }

        return $this->redirectToRoute('app_mission_suspend', [
            'id' => $mission->getId(),
        ]);
    }

    #[Route('/suspend/{id}', name: 'app_mission_suspend')]
    public function suspend(Mission $mission): Response
    {

        $this->missionWorkflow->suspend($mission);
        $this->missionRepository->save($mission, true);

        return $this->render('mission/success.html.twig', [
            'message' => 'mission.pickup.message',
        ]);

    }

    #[Route('/load/express/{id}', name: 'app_mission_load_express')]
    public function assign(Mission $mission): Response
    {
        /*
        $when = $request->query->get('when', false);

        if ('tomorrow' === $when) {
            $date = new DateTime('tomorrow');
            $hour = rand(7,9);
            $minute = rand(0, 2) * 20;
            $date->setTime($hour, $minute);

            $mission->setDeliveryAt(DateTimeImmutable::createFromMutable($date));
            $mission->setScheduledAt(DateTimeImmutable::createFromMutable($date));
        }

        if ('today' === $when) {
            $date = new DateTime('today');
            $hour = rand(13, 15);
            $minute = rand(0, 2) * 20;
            $date->setTime($hour, $minute);

            $mission->setDeliveryAt(DateTimeImmutable::createFromMutable($date));
            $mission->setScheduledAt(DateTimeImmutable::createFromMutable($date));
        }
        */

        $message = false;
        $now = new DateTime('now');

        if ((int) $now->format('G') > 16 ) {
            return $this->redirectToRoute('app_mission_suspend', [
                'id' => $mission->getId(),
            ]);
        }

        $now->modify('+1 hour');
        $mission->setDeliveryAt(DateTimeImmutable::createFromMutable($now));
        $mission->setScheduledAt(DateTimeImmutable::createFromMutable($now));
        $message = 'mission.express.message';

        $vehicle = $mission->getPickingVehicle();
        $mission->setDeliveringVehicle($vehicle);
        $this->missionWorkflow->load($mission);
        $this->missionRepository->save($mission, true);

        return $this->render('mission/success.html.twig', [
            'message' => $message
        ]);
    }



    #[Route('/delivery/{id}', name: 'app_mission_delivery')]
    public function delivery(Mission $mission): Response
    {
        $this->missionWorkflow->delivery($mission);
        $this->missionRepository->save($mission, true);

        return $this->render('mission/success.html.twig', [
            'message' => 'mission.delivery.message',
        ]);
    }


    #[Route('/add/{id}', name: 'app_mission_add_picture')]
    public function addPicture(Mission $mission, Request $request): Response
    {
        $image = new PictureUpload();
        $form = $this->createForm(UploadImageType::class, $image);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->getData();
            $mission->addPicture($image);
            $this->missionRepository->save($mission, true);

            return $this->redirectToRoute('app_mission_detail', [
                'id' => $mission->getId(),
            ]);
        }

        return $this->render('mission/picture.html.twig', [
            'form' => $form,
            'mission' => $mission,
        ]);
    }

}
