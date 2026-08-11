<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Place;
use App\Form\DataTransformer\StringToPlaceTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    public function __construct(
        private readonly StringToPlaceTransformer $stringToPlaceTransformer
    )
    {
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->resetViewTransformers()
            ->addModelTransformer($this->stringToPlaceTransformer)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
//            'data_class' => Place::class,
        ]);
    }
}
