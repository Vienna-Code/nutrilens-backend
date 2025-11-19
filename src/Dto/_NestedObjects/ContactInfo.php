<?php

namespace App\Dto\_NestedObjects;

use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class ContactInfo
{
    use FillFromArray;

    #[Assert\Type('string')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9\s\-]{6,20}$/',
        message: 'Invalid phone number'
    )]
    public string $number;

    #[Assert\Email]
    public string $email;
}
