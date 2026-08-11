<?php

namespace App\Form;

use App\Entity\Package;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;

class MultiPickingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity',IntegerType::class, [
                'block_prefix' => 'multipicking',
                'row_attr' => ['class' => 'col-6 col-lg-2'],
            ])
            ->add('aspect', ChoiceType::class, [
                'block_prefix' => 'multipicking',
                'row_attr' => ['class' => 'col-6 col-lg-2'],
                'choices' => array_combine(Package::ASPECTS, Package::ASPECTS),
                'placeholder' => t('label.form.empty_value', [], 'EasyAdminBundle'),
            ])
            ->add('deliveryPlace', EntityType::class, [
                'block_prefix' => 'multipicking',
                'class' => Place::class,
                'placeholder' => t('label.form.empty_value', [], 'EasyAdminBundle'),
                'row_attr' => ['class' => 'col-12 col-lg-4'],
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'data-ea-autocomplete-allow-item-create' => 'true',
                    'data-ea-autocomplete-create-filter' => '^(.+)\s-\s(.*)\s\([A-Z]{2}\)$',
                ]
            ])
            ->add('note', TextType::class, [
                'block_prefix' => 'multipicking',
                'row_attr' => ['class' => 'col-12 col-lg-4'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'row gx-3'],
            // Configure your form options here
            // 'data_class' => Mission::class,
        ]);
    }
}
