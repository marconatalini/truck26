<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AddressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Address::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('addressLine1'),
//            TextField::new('addressLine2'),
            TextField::new('city')->setColumns(6),
            TextField::new('province')->setColumns(2)
                ->setHelp('province.help'),
            TextField::new('coordinates')->hideWhenCreating()
                ->setHelp('coordinates.help'),
        ];
    }

}
