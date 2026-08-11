<?php

namespace App\Controller;

use App\Repository\MissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VehicleController extends AbstractController
{
    #[Route('/vehicle', name: 'app_vehicle_index')]
    public function index(MissionRepository $missionRepository): Response
    {
        $result = $missionRepository->findLastVehiclePlace();

        return $this->render('vehicle/index.html.twig', [
            'result' => $result
        ]);
    }

}
