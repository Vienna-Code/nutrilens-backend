<?php

namespace App\Dto\Reviews;
use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class ListReviews
{
    use FillFromArray;

    public int $id;
}