<?php

namespace App\Dto\Auth;
use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class LogIn
{
    use FillFromArray;

    #[Assert\NotBlank]
    public string $username;

    #[Assert\NotBlank]
    public string $password;
}