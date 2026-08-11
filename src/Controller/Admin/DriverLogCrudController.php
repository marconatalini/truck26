<?php

namespace App\Controller\Admin;

use App\Entity\DriverLog;
use App\Form\ReportLogType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\Translation\t;

class DriverLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DriverLog::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('driver')
                ->setQueryBuilder(fn (QueryBuilder $queryBuilder) => $queryBuilder
                ->where('entity.firedAt IS NULL')
                ->andWhere('CONTAINS(entity.roles, \'["ROLE_DRIVER"]\') = TRUE')
            ),
            ChoiceField::new('category')->setTranslatableChoices(
                [
                    'drive' => t('category.drive'),
                    'holiday' => t('category.holiday'),
                    'permit' => t('category.permit'),
                    'donation' => t('category.donation'),
                ]
            ),
            AssociationField::new('vehicle'),
            DateTimeField::new('startDate'),
            DateTimeField::new('endDate'),
            TextAreaField::new('note'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('driver')
            ->add(ChoiceFilter::new('category')->setTranslatableChoices(
                [
                    'drive' => t('category.drive'),
                    'holiday' => t('category.holiday'),
                    'permit' => t('category.permit'),
                    'donation' => t('category.donation'),
                ]
            ))
            ->add('vehicle')
            ->add('startDate')
            ->add('endDate')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $report = Action::new('report')
            ->linkToCrudAction('viewReport')
            ->createAsGlobalAction()
            ->displayIf(function (): bool {
                return $this->getContext()->getRequest()->query->has('filters');
            })
            ;

        return $actions
            ->add(Crud::PAGE_INDEX, $report)
            ;
    }

    #[AdminRoute('/report')]
    public function viewReport(Request $request): Response
    {

        return $this->render('admin/driverLog/report.html.twig', [
        ]);
    }


}
