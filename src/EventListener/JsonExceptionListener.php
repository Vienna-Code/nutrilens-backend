<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class JsonExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        
        if (str_contains($request->getContentTypeFormat(), 'json')) {
            $exception = $event->getThrowable();

            $statusCode = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            $response = new JsonResponse([
                'error' => [
                    'type' => (new \ReflectionClass($exception))->getShortName(),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $_ENV['APP_DEBUG'] ? $exception->getFile() : null,
                    'line' => $_ENV['APP_DEBUG'] ? $exception->getLine() : null,
                    'trace' => $_ENV['APP_DEBUG'] ? $exception->getTrace() : null,
                ],
            ], $statusCode);

            $event->setResponse($response);
        }
    }
}
