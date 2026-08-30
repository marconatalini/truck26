<?php

namespace App\Workflow;

use App\Entity\Mission;
use App\Repository\MissionRepository;
use App\Workflow\Transition\MissionTransition;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

class MissionWorkflow
{
    public function __construct(
        #[Target('mission')] private readonly WorkflowInterface $workflow,
        readonly MissionRepository                              $missionRepository
    )
    {
    }

    public function initiate(Mission $mission): void
    {
        $this->workflow->getMarking($mission);
    }

    public function assign(Mission $mission): void
    {
        $this->workflow->apply($mission, MissionTransition::MISSION_ASSIGN);
        $this->missionRepository->save($mission);
    }

    public function unassign(Mission $mission): void
    {
        $mission->setPickupAt(null);
        $mission->setScheduledAt(null);
        $this->workflow->apply($mission, MissionTransition::MISSION_UNASSIGN);
        $this->missionRepository->save($mission, flush: true);
    }


    public function pickup(Mission $mission): void
    {
        $this->workflow->apply($mission, MissionTransition::MISSION_PICKUP);
        $mission->setPicked(true);
        $this->missionRepository->save($mission);
    }

    public function suspend(Mission $mission): void
    {
        $this->workflow->apply($mission, MissionTransition::MISSION_SUSPEND);
        $mission->setDeliveringVehicle(null);
        $this->missionRepository->save($mission);
    }

    public function load(Mission $mission): void
    {
        $this->workflow->apply($mission, MissionTransition::MISSION_LOAD);
        $this->missionRepository->save($mission);
    }

    public function unload(Mission $mission): void
    {
        $mission->setDeliveryAt(null);
        $mission->setScheduledAt(null);
        $this->workflow->apply($mission, MissionTransition::MISSION_UNLOAD);
        $this->missionRepository->save($mission, flush: true);
    }



    public function delivery(Mission $mission): void
    {
        $this->workflow->apply($mission, MissionTransition::MISSION_DELIVERY);
        $mission->setDelivered(true);
        $this->missionRepository->save($mission);
    }

    public function delete(Mission $mission): void
    {
        $this->missionRepository->remove($mission, true);
    }




}
