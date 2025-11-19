<?php

namespace App\Dto\Commerces;

use App\Dto\_NestedObjects\ContactInfo;
use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class PostCommerces
{
    use FillFromArray;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $type;

    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: -90, max: 90)]
    public float $coordsLat;

    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: -180, max: 180)]
    public float $coordsLon;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $address;

    #[Assert\Type('array')]
    #[Assert\Valid]
    public array $contactInfo = [];

    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\NotBlank
    ])]
    public array $paymentMethods = [];

    #[Assert\Type('array')]
    #[Assert\Valid]
    public array $commerceSchedules = [];
}
