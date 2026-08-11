<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use function Symfony\Component\Translation\t;

class ForeignKeyErrorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        readonly EntityManagerInterface $entityManager,
        readonly AdminUrlGeneratorInterface $adminUrlGenerator,
        readonly RequestStack $requestStack,
    )
    {
    }

    /**
     * @param BeforeEntityDeletedEvent $event
     * @return void
     */
    public function onBeforeEntityDeletedEvent($event): void
    {
        $object = $event->getEntityInstance();
        $session = $this->requestStack->getSession();

        try {
            $this->entityManager->remove($object);
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            preg_match('/\bfrom\s+table\s+"([^"]+)"/i', $e->getMessage(), $matches);
            $session->getFlashBag()->add('danger', t('foreignKey.error.onTable', [
                '%table%' => $matches[1],
            ]));
            $url = $this->adminUrlGenerator
//                ->setController( UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
            $event->setResponse(new RedirectResponse($url));
        }
    }


    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityDeletedEvent::class => 'onBeforeEntityDeletedEvent',
        ];
    }
}
