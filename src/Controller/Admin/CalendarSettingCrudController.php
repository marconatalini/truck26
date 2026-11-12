<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use App\Repository\SettingRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;

class CalendarSettingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Setting::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->where('entity.key LIKE \'CALENDAR_%\'');
    }


    public function configureActions(Actions $actions): Actions
    {
        $reset = Action::new('reset_single_value','reset')
            ->linkToCrudAction('resetSingleValue')
            ->askConfirmation('reset.setting.confirmation')
            ;

        $resetAll = Action::new('reset_all_values','reset')
            ->linkToCrudAction('resetAllValues')
            ->asDangerAction()
            ->createAsGlobalAction()
            ->askConfirmation('reset.setting.confirmation')
            ;



        return $actions
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_EDIT, $reset)
            ->add(Crud::PAGE_INDEX, $resetAll)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('description')->setDisabled(),
            TextField::new('key')->setDisabled(),
            TextField::new('value'),
        ];
    }

    #[AdminRoute('/{id}/reset')]
    public function resetSingleValue(Setting $setting, SettingRepository $repository): Response
    {
        $defaultValue = $setting->getDefaultValue();
        $setting->setValue($defaultValue);

        $repository->save($setting);

        return $this->redirectToRoute('admin_calendar_setting_edit', ['entityId' => $setting->getId()]);

    }

    #[AdminRoute('/resetAll')]
    public function resetAllValues(AdminContext $context, SettingRepository $repository): Response
    {
        $settings = $repository->findAll();
        foreach ($settings as $setting) {
            $defaultValue = $setting->getDefaultValue();
            $setting->setValue($defaultValue);
            $repository->save($setting, false);
        }

        $repository->save($setting); //re-save last setting and flush All

        return $this->redirectToRoute('admin_calendar_setting_index');

    }



}
