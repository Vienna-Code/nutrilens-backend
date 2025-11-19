<?php

namespace App\Dto\_NestedObjects;

use App\Dto\FillFromArray;
use Symfony\Component\Validator\Constraints as Assert;

class CommerceSchedule
{
    use FillFromArray;

    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Assert\Range(min: 0, max: 6)]
    public int $weekday;

    #[Assert\NotBlank]
    #[Assert\DateTime(format: \DateTime::ATOM)]
    public string $opensAt;

    #[Assert\NotBlank]
    #[Assert\DateTime(format: \DateTime::ATOM)]
    public string $closesAt;
}
