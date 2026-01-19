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
        if ($event->hasResponse()) {
            return;
        }

        $exception = $event->getThrowable();

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $error = [
            'type'    => (new \ReflectionClass($exception))->getShortName(),
            'message' => $exception->getMessage(),
        ];

        if ($_ENV['APP_DEBUG']) {
            $error += [
                'code'  => $exception->getCode(),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        $event->setResponse(new JsonResponse(
            ['error' => $error],
            $statusCode
        ));
    }
}
