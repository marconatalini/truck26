<?php

namespace App\Controller\Admin;

use App\Entity\Mission;
use App\Entity\Package;
use App\Entity\Vehicle;
use App\Form\MultiPickingType;
use App\Form\PlaceType;
use App\Workflow\State\MissionState;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\ActionFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FieldFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\HttpFoundation\Response;

class MultiMissionCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Mission::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
    }


    public function configureFields(string $pageName): iterable
    {
        $vehicleId = $this->getContext()->getRequest()->query->get('vehicleId');

        if ($pageName === Crud::PAGE_NEW && null !== $vehicleId) {
            $vehicle = $this->container->get('doctrine')->getRepository(Vehicle::class)->find($vehicleId);
            yield AssociationField::new('pickingVehicle')
                    ->setFormTypeOption('data', $vehicle)
                ;
            yield AssociationField::new('pickupPlace')
                ->setFormType(PlaceType::class)
                ->setFormTypeOption('attr', [
                    'data-ea-autocomplete-allow-item-create' => 'true',
                    'data-ea-autocomplete-create-filter' => '^(.+)\s-\s(.*)\s\([A-Z]{2}\)$',
                ]);
            yield CollectionField::new('pickedStuff')->setColumns(12)
                    ->setFormTypeOption('mapped' , false)
                    ->addFormTheme('admin/theme/picking_theme.html.twig')
                    ->setEntryType(MultiPickingType::class);
        }
    }

    public function new(AdminContext $context): KeyValueStore|Response
    {
        /*$event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }*/

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::NEW, 'entity' => null, 'entityFqcn' => $context->getEntity()->getFqcn()])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        /** @var class-string<TEntity> $entityFqcn */
        $entityFqcn = $context->getEntity()->getFqcn();
        $context->getEntity()->setInstance($this->createEntity($entityFqcn));
        $this->container->get(FieldFactory::class)->processFields($context->getEntity(), new FieldCollection($this->configureFields(Crud::PAGE_NEW)), Crud::PAGE_NEW);
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $this->container->get(ActionFactory::class)->processEntityActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $newForm = $this->createNewForm($context->getEntity(), $context->getCrud()->getNewFormOptions(), $context);
        $newForm->handleRequest($context->getRequest());

        /** @var Mission $entityInstance */
        $entityInstance = $newForm->getData();
        $context->getEntity()->setInstance($entityInstance);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            /*$this->processUploadedFiles($newForm);*/

            /*$event = new BeforeEntityPersistedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $entityInstance = $event->getEntityInstance();*/

            $toDelivered = $newForm->get('pickedStuff')->getData();
            $places = array_column($toDelivered, 'deliveryPlace');
            array_multisort($places, SORT_ASC, $toDelivered);
            $lastPlace = null;
            foreach ($toDelivered as $delivery) {
                if ($lastPlace != $delivery['deliveryPlace']) {
                    $mission = clone $entityInstance;
                }
                $mission->setStatus(MissionState::PENDING);
                $mission->setDeliveryPlace($delivery['deliveryPlace']);
                $mission->setPickupAt(new \DateTimeImmutable());
                $mission->setPicked(true);

                $package = new Package();
                $package->setDefaults($delivery['aspect']);
                $package->setQuantity($delivery['quantity']);
                $mission->addPackage($package);
                $mission->setWeight($mission->getWeight() +
                    $package->getWeight() * $package->getQuantity());
                $mission->setArea($mission->getArea() +
                    $package->getLength() * $package->getWidth()/1000000 * $package->getQuantity());

                $this->persistEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $mission);
                $lastPlace = $delivery['deliveryPlace'];
            }

            /*$event = new AfterEntityPersistedEvent($entityInstance);
            $this->container->get('event_dispatcher')->dispatch($event);
            $context->getEntity()->setInstance($entityInstance);
            if ($event->isPropagationStopped()) {
                return $event->getResponse();
            }*/
            $count = count($toDelivered);
            if ($count > 0) {
                $this->addFlash('success',sprintf('Added %d mission from %s', $count, $entityInstance->getPickupPlace()->getName()) );
            }

            return $this->redirectToRoute('admin_vehicle_index');
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_NEW,
            'templateName' => 'crud/new',
            'entity' => $context->getEntity(),
            'new_form' => $newForm,
        ]));

        /*$event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }*/

        return $responseParameters;
    }


}
