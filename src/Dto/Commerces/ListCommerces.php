<?php

namespace App\Dto\Commerces;
use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class ListCommerces
{
    use FillFromArray;

    #[Assert\Type('string')]
    #[Assert\Regex(
        pattern: '/^-?\d+(?:\.\d+)?,-?\d+(?:\.\d+)?$/',
        message: 'This value must be two floats separated by a comma.'
    )]
    public string $lat;

    #[Assert\Type('string')]
    #[Assert\Regex(
        pattern: '/^-?\d+(?:\.\d+)?,-?\d+(?:\.\d+)?$/',
        message: 'This value must be two floats separated by a comma.'
    )]
    public string $lon;

    #[Assert\Type('string')]
    public string $name;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 0)]
    public int $minPrice;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 0)]
    public int $maxPrice;
    
    #[Assert\Type('string')]
    public string $restrictions; // Various strings separated by commas

    #[Assert\Type('string')]
    public string $commerceTypes; // Various strings separated by commas

    public bool $unverified;
}