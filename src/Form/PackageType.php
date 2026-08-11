<?php

namespace App\Form;

use App\Entity\Mission;
use App\Entity\Package;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', null ,[
                'empty_data' => 0,
                ])
            ->add('aspect', ChoiceType::class, [
                'placeholder' => 'Scegli...',
                'choices' => array_combine(Package::ASPECTS, Package::ASPECTS),
                'choice_attr' => function ($choice, string $key, mixed $value) {
                    // adds a class like attending_yes, attending_no, etc
                    return ['data-default' => Package::DEFAULTS[$key]];
                },
            ])
            ->add('length', null , [
                'help' => 'millimeters'
            ])
            ->add('width', null , [
                'empty_data' => 0,
                'help' => 'millimeters'
            ])
            ->add('height', null , [
                'empty_data' => 0,
                'help' => 'millimeters'
            ])
            ->add('weight', null , [
                'empty_data' => 0,
                'help' => 'kilograms'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Package::class,
            'attr' => ['class' => 'd-flex package-item'],
        ]);
    }
}
