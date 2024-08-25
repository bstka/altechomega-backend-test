<?php

namespace App\EventListener;

use ArrayObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
  public function onKernelException(ExceptionEvent $event)
  {
    $exception = $event->getThrowable();
    
    $response = new JsonResponse([
      'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
      'message' => Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
      'data' => new ArrayObject()
    ], Response::HTTP_INTERNAL_SERVER_ERROR);
    
    if ($exception instanceof HttpExceptionInterface) {
      $statusCode = $exception->getStatusCode();

      $response = new JsonResponse([
        'code' => $statusCode,
        'message' => Response::$statusTexts[$statusCode],
        'data' => new ArrayObject()
      ], $statusCode);
    }

    $event->setResponse($response);
  }
}
