<?php

namespace App\Controller\Admin;

use App\Entity\PackageAspect;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PackageAspectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PackageAspect::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('aspect'),
            IntegerField::new('length'),
            IntegerField::new('width'),
            IntegerField::new('height'),
            IntegerField::new('weight'),
            ChoiceField::new('scope')
                ->setChoices(['single' => 'single', 'multi' => 'multi',])
                ->setHelp('scope.help')
            ,
        ];
    }

}
