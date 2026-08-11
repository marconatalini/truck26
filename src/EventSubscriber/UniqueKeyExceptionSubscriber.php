<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UniqueKeyExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        readonly AdminUrlGeneratorInterface $adminUrlGenerator,
    )
    {
    }


    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (!$this->isDatabaseException($throwable)) {
            return;
        }

        $request = $event->getRequest();

        // Aggiungiamo il flash message alla sessione
        if ($request->hasSession()) {
            $request->getSession()->getFlashBag()->add(
                'error',
                $throwable->getMessage()
            );
        }

        // Se vuoi reindirizzare alla pagina precedente:
//        $redirectUrl = $request->headers->get('referer') ?? $this->adminUrlGenerator->generateUrl();

        $response = new RedirectResponse(
            $this->adminUrlGenerator
                ->unsetAllExcept('object')
                ->generateUrl()
        );

        dump($event->getRequest()->getQueryString());

        // Impostando la response sul KernelEvent, interrompiamo la propagazione dell'eccezione
        $event->setResponse($response);

    }

    private function isDatabaseException(\Throwable $throwable): bool
    {
        if ($throwable instanceof UniqueConstraintViolationException) {
            return true;
        }

        // Doctrine spesso racchiude la PDOException all'interno di una DBALException
//        $previous = $throwable->getPrevious();
//        while ($previous !== null) {
//            if ($previous instanceof PDOException || $previous instanceof DBALException) {
//                return true;
//            }
//            $previous = $previous->getPrevious();
//        }

        return false;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION  => 'onExceptionEvent',
        ];
    }
}
