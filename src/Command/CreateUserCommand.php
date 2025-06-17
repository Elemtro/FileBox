<?php

namespace App\Command;

use App\Api\Service\UserService;
use App\Storage\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserService $userService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('role', InputArgument::OPTIONAL, 'User role (USER or ADMIN)', 'USER');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $roleStr = strtoupper($input->getArgument('role'));

        try {
            $role = UserRole::from('ROLE_' . $roleStr);
        } catch (\ValueError) {
            $io->error("Role is not correct: $roleStr. Example: USER, ADMIN...");
            return Command::FAILURE;
        }

        $existing = $this->userService->isExistByEmail($email);
        if ($existing) {
            $io->error("User with email $email alredy exists.");
            return Command::FAILURE;
        }
        $user = new User();
        $user->setEmail($email);
        $user->setRole($role);
        $user->setPassword($this->hasher->hashPassword($user, $password));

        $this->userService->saveUser($user);

        $io->success("User $email has been created with role $roleStr.");

        return Command::SUCCESS;
    }
}
