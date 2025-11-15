<?php

namespace App\Dto\Products;
use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class ListProducts
{
    use FillFromArray;

    public int $id;
}