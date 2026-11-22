<?php

namespace App\Controller;

use App\Repository\DriverLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DriverLogController extends AbstractController
{
    #[Route('/driver/log', name: 'app_driver_log')]
    public function index(DriverLogRepository $repository): Response
    {
        return $this->render('driver_log/json.html.twig', [
            'logs' => $repository->findAllDriverLogsFromToday()
        ]);
    }
}
