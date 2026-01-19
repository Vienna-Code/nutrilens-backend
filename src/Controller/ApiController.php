<?php

namespace App\Controller;

use App\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class ApiController extends AbstractController
{
    public function __construct(
        protected ValidatorInterface $validator
    ) {}

    protected function validate(array $input, Constraint $constraint): array
    {
        $violations = $this->validator->validate($input, $constraint);

        if (\count($violations) > 0) {
            $errors = [];

            foreach ($violations as $v) {
                $errors[] = [
                    'field' => trim($v->getPropertyPath(), '[]'),
                    'message' => $v->getMessage(),
                ];
            }

            throw new ValidationException([
                'error' => [
                    'message' => 'Error de validación.',
                    'errors' => $errors,
                ],
            ]);
        }

        return $input;
    }
}
