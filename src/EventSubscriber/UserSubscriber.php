<?php

namespace App\EventSubscriber;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }


    public function onBeforeEntityPersistedEvent(BeforeEntityPersistedEvent $event): void
    {
        if (!($event->getEntityInstance() instanceof User)) {
            return;
        }

        $user = $event->getEntityInstance();
        /** @var string $plainPassword */
        $plainPassword = $user->getPassword();

        // encode the plain password
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));

    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'onBeforeEntityPersistedEvent',
        ];
    }
}
