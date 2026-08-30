<?php

namespace App\EventSubscriber;

use App\Entity\Mission;
use App\Workflow\MissionWorkflow;
use App\Workflow\State\MissionState;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MissionSubscriber implements EventSubscriberInterface
{

    private int $totalWeight;
    private int $totalArea;

    public function __construct(
        private readonly MissionWorkflow $missionStateMachine,
    )
    {
//        $this->totalWeight = 0;
//        $this->totalArea = 0;
    }


    public function onBeforeEntityPersistedEvent(BeforeEntityPersistedEvent $event): void
    {
        if (!$event->getEntityInstance() instanceof Mission) {
            return;
        }

        /** @var Mission $mission */
        $mission = $event->getEntityInstance();
        $this->missionStateMachine->initiate($mission);

        $this->getTotals($mission);
        $mission->setWeight($this->totalWeight);
        $mission->setArea($this->totalArea / 100);

    }

    public function onBeforeEntityUpdateEvent(BeforeEntityUpdatedEvent $event)
    {
        if (!$event->getEntityInstance() instanceof Mission) {
            return;
        }

        /** @var Mission $mission */
        $mission = $event->getEntityInstance();

        $this->getTotals($mission);
        $mission->setWeight($this->totalWeight);
        $mission->setArea($this->totalArea / 100);

        if (null === $mission->getPickingVehicle()) {
            $mission->setStatus(MissionState::START);
        }
    }

    private function getTotals(Mission $mission): void
    {
        $this->totalArea = 0;
        $this->totalWeight = 0;

        foreach ($mission->getPackages() as $package) {
            $this->totalWeight += $package->getWeight() * $package->getQuantity();
            $this->totalArea += ($package->getLength()/100 * $package->getWidth()/100) * $package->getQuantity();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'onBeforeEntityPersistedEvent',
            BeforeEntityUpdatedEvent::class => 'onBeforeEntityUpdateEvent',
        ];
    }
}
