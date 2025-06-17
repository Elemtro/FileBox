<?php
// src/Service/FileService.php
namespace App\Api\Service;

use App\Storage\Repository\UserRepository;


class UserService
{
    public function __construct(
        public readonly UserRepository $userRepository
    ) {}
    public function findUserByUuid($uuid){
        return $this->userRepository->findOneByUuid($uuid);
    }
}
