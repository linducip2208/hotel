<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HotelException extends \RuntimeException
{
    protected string $errorCode;

    protected int $httpStatusCode;

    protected array $context;

    public function __construct(
        string $message = '',
        string $errorCode = '',
        int $httpStatusCode = 400,
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);

        $this->errorCode = $errorCode;
        $this->httpStatusCode = $httpStatusCode;
        $this->context = $context;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => $this->getErrorCode(),
                    'message' => $this->getMessage(),
                ],
                'context' => $this->context ?: null,
            ], $this->getHttpStatusCode());
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $this->getMessage());
    }
}
