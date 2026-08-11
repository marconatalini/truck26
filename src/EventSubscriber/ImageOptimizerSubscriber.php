<?php

namespace App\EventSubscriber;

use App\Entity\PictureUpload; // La tua entità
use Intervention\Image\Drivers\Gd\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // O Imagick\Driver se installato
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

class ImageOptimizerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::PRE_UPLOAD => 'onPreUpload',
        ];
    }

    public function onPreUpload(Event $event): void
    {
        $object = $event->getObject();

        // Applica la logica solo alle entità che ti interessano
        if (!$object instanceof PictureUpload) {
            return;
        }

        $mapping = $event->getMapping();
        $file = $mapping->getFile($object);

        if (!$file) {
            return;
        }

        // 1. Inizializza ImageManager con il driver scelto
        $manager = new ImageManager(new Driver());

        // 2. Apri l'immagine dal percorso temporaneo $file->getRealPath()
        $image = $manager->decode($file->getRealPath());

        // 3. Elaborazione: ridimensiona a max 1200px di larghezza (proporzionale)
        $image->scale(width: 1200);

        // 4. Salvataggio: sovrascrivi il file temporaneo
        // Ottimizziamo salvando in WebP (molto più leggero) o mantenendo il formato originale
        // Qui lo salviamo con qualità 75% per risparmiare spazio
        $encoder = new JpegEncoder(75, true, true);
        $encoded = $image->encode($encoder);

        // Sovrascriviamo il file che Vich sta per caricare
         file_put_contents($file->getRealPath(), (string) $encoded);
    }
}
