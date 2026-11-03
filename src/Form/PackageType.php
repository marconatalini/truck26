<?php

namespace App\Form;

use App\Entity\Package;
use App\Entity\PackageAspect;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;

class PackageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', null ,[
                'empty_data' => 0,
                ])
            ->add('aspect', EntityType::class, [
                'class' => PackageAspect::class,
                'choice_label' => 'aspect',
                'query_builder' => function (EntityRepository $repository): QueryBuilder {
                    return $repository->createQueryBuilder('p')
                        ->where('p.scope is null')
                        ->orWhere('p.scope = :scope')
                        ->setParameter('scope', 'single');
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
