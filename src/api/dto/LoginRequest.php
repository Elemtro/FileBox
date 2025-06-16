<?php
// src/api/dto/LoginRequest.php
namespace App\Api\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest
{
    #[Assert\NotBlank(message: "Email should not be blank.")]
    #[Assert\Email(message: "Please enter a valid email address.")]
    #[Assert\Length(
        max: 70,
        maxMessage: "Email cannot be longer than {{ limit }} characters."
    )]
    public string $email;

    #[Assert\NotBlank(message: "Password should not be blank.")]
    #[Assert\Length(
        min: 8,
        minMessage: "Password must be at least {{ limit }} characters long.",
        max: 255,
        maxMessage: "Password cannot be longer than {{ limit }} characters."
    )]
    public string $password;
}
