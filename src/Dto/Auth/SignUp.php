<?php

namespace App\Dto\Auth;
use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class SignUp
{
    use FillFromArray;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 40)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 256)]
    public string $password;
}