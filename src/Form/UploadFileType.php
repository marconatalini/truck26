<?php

namespace App\Form;

use App\Entity\MediaUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UploadFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('imageFile', VichFileType::class, [
                'allow_delete' => false,
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
            'data_class' => MediaUpload::class
        ]);
    }
}
