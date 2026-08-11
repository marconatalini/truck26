<?php

namespace App\Controller\Admin;

use App\Entity\DriverLog;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('username'),
            TextField::new('password')->onlyWhenCreating(),
            ChoiceField::new('roles')
                ->setPermission('ROLE_ADMIN')
                ->allowMultipleChoices(true)
                ->setChoices([
                    'ADMIN' => 'ROLE_ADMIN',
                    'DRIVER' => 'ROLE_DRIVER',
                ]),
            DateField::new('firedAt')
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $reset_password = Action::new('resetPassword')
            ->linkToCrudAction('resetPassword')
            ;

        $calendar = Action::new('calendar', null, 'fas fa-calendar-days')
            ->linkToCrudAction('userCalendar')
            ;

        return $actions
            ->add(Crud::PAGE_EDIT, $reset_password)
            ->add(Crud::PAGE_INDEX, $calendar)
            ;
    }

    #[AdminRoute(path: 'user_calendar', name: 'user_calendar')]
    public function userCalendar(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();

        return $this->render('admin/user/driver_logs.html.twig', [
            'driver' => $user,
        ]);

    }

    #[AdminRoute('/driver/logs/{id}')]
    public function driverLogs(User $user, Request $request): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $missionRepository = $this->container->get('doctrine')->getManager()->getRepository(DriverLog::class);
        $result = $missionRepository->findCalendarDriverLogs($start, $end, $user);

        return $this->json($result);
    }


    #[AdminRoute(path: 'reset_password', name: 'reset_password')]
    public function resetPassword(AdminContext $context, UserPasswordHasherInterface $passwordHasher)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $editForm = $this->createFormBuilder($user)
            ->add('username', TextType::class, [
                'disabled' => true,
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
//                'invalid_message' => 'Le password sono diverse! Riscrivile.',
//                'options' => ['attr' => ['class' => 'password-field']],
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('Reset', SubmitType::class)
            ->getForm()
        ;

        $editForm->handleRequest($context->getRequest());
        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $pleinPassword = $user->getPassword();

            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $pleinPassword
            ));

            $this->updateEntity($this->container->get('doctrine')->getManagerForClass(User::class), $user);

            $url = $this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->generateUrl();

            $this->addFlash('success', 'You have successfully modified password of '. $user);

            return $this->redirect($url);
        }

        return $this->render('admin\user\reset_password.html.twig', [
            'form' => $editForm,
        ]);
    }
}
