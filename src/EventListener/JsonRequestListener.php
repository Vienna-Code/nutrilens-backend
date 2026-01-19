<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsEventListener]
final class JsonRequestListener
{
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only for these methods
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            return;
        }

        // Only for JSON requests
        if ($request->getContentTypeFormat() !== 'json') {
            throw new BadRequestHttpException('Invalid content type, expected JSON.');
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException('Invalid JSON body: ' . json_last_error_msg());
        }
    }
}