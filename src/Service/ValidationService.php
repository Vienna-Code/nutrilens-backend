<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {}

    public function validate(object $dto): JsonResponse|null {
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return new JsonResponse([
                'error' => [
                    'message' => 'Request data validation failed.',
                    'errors' => $errors
                ]
            ], 400);
        }
        return null;
    }
}