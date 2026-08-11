<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create:user',
    description: 'Create user',
)]
class AdminCreateCommand extends Command
{
    public function __construct(
        readonly UserPasswordHasherInterface $passwordHasher,
        readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username for login')
            ->addArgument('psw', InputArgument::REQUIRED, 'Password for login')
            ->addArgument('role', InputArgument::REQUIRED, 'User role: ')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $psw = $input->getArgument('psw');
        $role = $input->getArgument('role');

        if (null !== $username && null !== $psw && null !== $role) {
            $user = new User();
            $user->setUsername($username);
            $user->setPassword($this->passwordHasher->hashPassword($user, $psw));
            $user->setRoles(['ROLE_' . strtoupper($role)]);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            $io->error('Please enter username, password and role (es. ADMIN or DRIVER)');
        }

        $io->success('You have a new user!');

        return Command::SUCCESS;
    }
}
