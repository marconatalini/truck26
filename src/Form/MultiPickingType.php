<?php

namespace App\Form;

use App\Entity\PackageAspect;
use App\Entity\Place;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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
            /*->add('aspect', ChoiceType::class, [
                'block_prefix' => 'multipicking',
                'row_attr' => ['class' => 'col-6 col-lg-2'],
                'choices' => array_combine(Package::ASPECTS, Package::ASPECTS),
                'placeholder' => t('label.form.empty_value', [], 'EasyAdminBundle'),
            ])*/
            ->add('aspect', EntityType::class, [
                'block_prefix' => 'multipicking',
                'row_attr' => ['class' => 'col-6 col-lg-2'],
                'class' => PackageAspect::class,
                'choice_label' => 'aspect',
                'query_builder' => function (EntityRepository $repository): QueryBuilder {
                    return $repository->createQueryBuilder('p')
                        ->where('p.scope is null')
                        ->orWhere('p.scope = :scope')
                        ->setParameter('scope', 'multi');
                },
                'choice_attr' => function ($choice, string $key, mixed $value) {
                    return ['data-default' =>
                        $choice->getLength() . 'x' .
                        $choice->getWidth() . 'x' .
                        $choice->getHeight() . 'x' .
                        $choice->getWeight()
                    ];
                },
                'placeholder' => t('label.form.empty_value', [], 'EasyAdminBundle'),
            ])
            ->add('deliveryPlace', PlaceType::class, [
                'block_prefix' => 'multipicking',
                'class' => Place::class,
                'placeholder' => t('label.form.empty_value', [], 'EasyAdminBundle'),
                'row_attr' => ['class' => 'col-12 col-lg-4'],
                'attr' => [
                    'data-ea-autocomplete-allow-item-create' => 'true',
                    'data-ea-autocomplete-create-filter' => '^(.+)\s-\s(.*)\s\([A-Z]{2}\)$',
                    'data-ea-widget' => 'ea-autocomplete',
                ],
                'constraints' => [
                    new Assert\NotNull(groups: ['multiPicking']),
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
