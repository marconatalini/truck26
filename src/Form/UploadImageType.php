<?php

namespace App\Form;

use App\Entity\MediaUpload;
use App\Entity\PictureUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UploadImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'allow_delete' => false,
                'download_uri' => true,
                'label' => false,
            ])
            ->add('description', null ,[
                'label' => 'image.description',
                'attr' => [
                    'placeholder' => 'image.description',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send',
                'row_attr' => [
                    'class' => 'btn btn-primary mt-5',
                ]
            ])
//            ->add('expireAt', null, [
//                'help' => 'Can be deleted after...'
//            ])
//            ->add('autoDelete')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PictureUpload::class
        ]);
    }
}
