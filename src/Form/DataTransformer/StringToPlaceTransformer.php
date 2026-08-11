<?php

namespace App\Form\DataTransformer;

use App\Entity\Address;
use App\Entity\Place;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StringToPlaceTransformer implements DataTransformerInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        readonly ValidatorInterface $validator
    ) {
    }

    /**
     * @inheritDoc
     */
    public function transform(mixed $value): mixed
    {
        // TODO: Implement transform() method.
        if ($value) {
            return $value->getId();
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform(mixed $value): mixed
    {
        // TODO: Implement reverseTransform() method.
        if (is_numeric($value)) {
            return $this->entityManager->getRepository(Place::class)->find($value);
        }

        if (null === $value) {
            return null;
        }

        $regex = "/^(.+)\s-\s(.*)\s\(([A-Z]{2})\)$/";

        if (preg_match($regex, $value, $matches)) {
            // $matches[0] contiene l'intera stringa che ha superato il controllo
            $name = $matches[1]; // Primo gruppo (.+)
            $city = $matches[2]; // Secondo gruppo (.*)
            $province = $matches[3]; // Terzo gruppo ([A-Z]{2})
        }

        $place = new Place();
        $address = new Address();

        $place->setName($name);
        $address->setCity($city);
        $address->setProvince($province);
        $place->setAddress($address);

        $errors = $this->validator->validate($place);

        if (count($errors) > 0) {
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $errors[0]->getPropertyPath()
            ));
        }

        $this->entityManager->persist($place);

        return $place;
    }
}
