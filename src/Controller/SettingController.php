<?php

namespace App\Controller;

use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettingController extends AbstractController
{
    #[Route('/setting/calendar', name: 'app_setting_calendar')]
    public function index(SettingRepository $repository): Response
    {
        return $this->render('setting/json.html.twig', [
            'settings' => $repository->findCalendarSetting(),
        ]);
    }
}
