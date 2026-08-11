<?php

namespace App\EventSubscriber;

use App\Entity\Address;
use App\Entity\Place;
use App\Service\GeocodingService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddressSubscriber implements EventSubscriberInterface
{
    public function __construct(readonly GeocodingService $geocodingService)
    {
    }

    public function onBeforeEntityPersistedEventClass($event): void
    {

        if (!$event->getEntityInstance() instanceof Place) {
            return;
        }

        /** @var Address $address */
        $address = $event->getEntityInstance()->getAddress();

        if (null === $address->getCoordinates()) {
            // Chiamata a Nominatim tramite il servizio
            $coordinates = $this->geocodingService->getCoordinates(
                $address->getAddressLine1(), $address->getCity(), $address->getProvince());

            $address->setCoordinates($coordinates);
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'onBeforeEntityPersistedEventClass',
            BeforeEntityUpdatedEvent::class => 'onBeforeEntityPersistedEventClass',
        ];
    }
}
