<?php

namespace App\Dto\Commerce;
use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class ListCommerces
{
    use FillFromArray;

    public string $lat;

    public string $lon;

    public bool $unverified;
}