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
    public function isExistByEmail($email){
        $response = $this->userRepository->findByEmail($email);
        if($response==null){
            return false;
        }
        return true;
    }
    public function saveUser($user){
        $this->userRepository->saveUser($user);
    }
}
