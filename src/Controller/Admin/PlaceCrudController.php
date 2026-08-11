<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\CoordsFilter;
use App\Entity\Address;
use App\Entity\Place;
use App\Form\ExcelUploadType;
use App\Service\ExcelExporterService;
use App\Service\ExcelImportService;
use App\Service\JsonToExcelService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\DocBlock\Serializer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class PlaceCrudController extends AbstractCrudController
{
    private const EXPECTED_HEADERS = [
        0 => 'nominativo',
        1 => 'indirizzo',
        2 => 'comune',
        3 => 'provincia',
        4 => 'coordinate'
    ];

    public static function getEntityFqcn(): string
    {
        return Place::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            AssociationField::new('address', '')
                ->renderAsEmbeddedForm(AddressCrudController::class)
                ->setColumns(12)
                ->onlyOnForms(),
            TextField::new('address.addressLine1')->hideOnForm(),
            TextField::new('address.city')->hideOnForm(),
            TextField::new('address.province')->hideOnForm(),
            TextAreaField::new('note'),

        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CoordsFilter::new('null.coordinates')->setLabel('coordinates.filter.label'))
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/index', 'admin/place/index.html.twig')
            ->setSearchFields([
                'name', 'address.addressLine1', 'address.city', 'address.province', 'note'
            ])
            ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('admin/js/place_index.js')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $import_action = Action::new('import', 'Import')
            ->setIcon('fa-solid fa-upload')
            ->linkToCrudAction('placeUpload')
            ->createAsGlobalAction();

        $export_action = Action::new('export', 'Export')
            ->setIcon('fa-solid fa-download')
            ->linkToCrudAction('placeDownload');

        $export_all_action = Action::new('exportAll', 'Export all')
            ->setIcon('fa-solid fa-download')
            ->linkToCrudAction('allPlaceDownload');


        $maps_action = Action::new('maps', 'Maps')
            ->setIcon('fa-solid fa-location-dot')
            ->setHtmlAttributes([
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ])
            ->linkToUrl(function (Place $entity) {
                $query = implode('+', array_map('urlencode', [
                    $entity->getAddress()->getAddressLine1(),
                    $entity->getAddress()->getCity(),
                    $entity->getAddress()->getProvince(),
                ]));
                return "https://www.google.com/maps/search/?api=1&query={$query}";
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $import_action)
            ->add(Crud::PAGE_EDIT, $maps_action)
            ->addBatchAction($export_action)
            ->addBatchAction($export_all_action)
            ;
    }

    #[AdminRoute('/download')]
    public function placeDownload(BatchActionDto $batchActionDto, SerializerInterface $serializer, JsonToExcelService $exporter)
    {
        $className = $batchActionDto->getEntityFqcn();
        $entityManager = $this->container->get('doctrine')->getManagerForClass($className);
        foreach ($batchActionDto->getEntityIds() as $id) {
            $place = $entityManager->getRepository($className)->findPlaceWithAddressById($id);
            $places[] = $place;
        }

        if (empty($places)) {
            $this->addFlash('warning', 'Nessun dato presente da esportare.');
            return $this->redirectToRoute('admin_place_index');
        }

        $json_data = $serializer->serialize($places, 'json');

        // Genera e scarica direttamente il file
        return $exporter->createStreamedResponseFromJson($json_data, 'truck26_luoghi_' . date('Y-m-d') . '.xlsx');

    }

    #[AdminRoute('/all/download')]
    public function allPlaceDownload(BatchActionDto $batchActionDto, SerializerInterface $serializer, JsonToExcelService $exporter)
    {
        $className = $batchActionDto->getEntityFqcn();
        $entityManager = $this->container->get('doctrine')->getManagerForClass($className);
        $places = $entityManager->getRepository($className)->findAll();

        if (empty($places)) {
            $this->addFlash('warning', 'Nessun dato presente da esportare.');
            return $this->redirectToRoute('admin_place_index');
        }

        $json_data = $serializer->serialize($places, 'json');

        // Genera e scarica direttamente il file
        return $exporter->createStreamedResponseFromJson($json_data, 'truck26_luoghi_' . date('Y-m-d') . '.xlsx');

    }



    #[AdminRoute('/upload')]
    public function placeUpload(Request $request, ExcelImportService $excelImportService) : Response
    {
        $form = $this->createForm(ExcelUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('excel_file')->getData();

            try {
                $errors = $excelImportService->validateExcelPlaceFile($file);
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $form->get('excel_file')->addError($error);
                    }
                } else {
                    $places = $excelImportService->getPlaces();
                    if (!empty($places)) {
                        $entityManager = $this->container->get('doctrine')->getManagerForClass(Place::class);
                        try {
                            foreach ($places as $place) {
                                $entityManager->persist($place);
                                $entityManager->flush();
                            }
                            // --- File valido! ---
                            $this->addFlash('success', 'File Excel validato e caricato con successo!');
                        } catch (UniqueConstraintViolationException $e) {
                            $this->addFlash('warning', $e->getMessage());
                        }

                    }
                    // Qui puoi leggere i dati o fare un redirect per evitare il re-submit
                    return $this->redirectToRoute('admin_place_index');
                }

            } catch (\Exception $e) {
                $form->addError(new FormError('Errore nella lettura del file Excel: ' . $e->getMessage()));
            }
        }

        return $this->render('admin/place/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }



}
