<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected array $dontReport = [
        HotelException::class,
        ValidationException::class,
    ];

    protected array $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'api_key',
        'secret',
        'token',
        'authorization',
        'credit_card',
        'cvv',
        'ssn',
        'ktp_number',
        'npwp_number',
        'passport_number',
        'bank_account',
    ];

    public function render($request, Throwable $e): JsonResponse|RedirectResponse|\Illuminate\Http\Response
    {
        if ($e instanceof HotelException) {
            return $this->renderHotelException($request, $e);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->renderModelNotFound($request, $e);
        }

        if ($e instanceof ValidationException) {
            return $this->renderValidationException($request, $e);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->renderNotFound($request);
        }

        if ($e instanceof AuthenticationException) {
            return $this->renderAuthenticationException($request, $e);
        }

        if ($e instanceof AuthorizationException) {
            return $this->renderAuthorizationException($request, $e);
        }

        if ($e instanceof TokenMismatchException) {
            return $this->renderTokenMismatch($request);
        }

        return parent::render($request, $e);
    }

    protected function renderHotelException(Request $request, HotelException $e): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ],
                'context' => $e->getContext() ?: null,
            ], $e->getHttpStatusCode());
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $e->getMessage());
    }

    protected function renderModelNotFound(Request $request, ModelNotFoundException $e): JsonResponse|RedirectResponse
    {
        $modelName = class_basename($e->getModel());
        $ids = $e->getIds();

        $message = count($ids) > 0
            ? sprintf('%s with ID(s) %s not found.', $modelName, implode(', ', $ids))
            : sprintf('%s not found.', $modelName);

        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'MODEL_NOT_FOUND',
                    'message' => $message,
                ],
            ], 404);
        }

        abort(404, $message);
    }

    protected function renderValidationException(Request $request, ValidationException $e): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ],
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors($e->errors(), $e->errorBag);
    }

    protected function renderNotFound(Request $request): JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'The requested resource was not found.',
                ],
            ], 404);
        }

        abort(404);
    }

    protected function renderAuthenticationException(Request $request, AuthenticationException $e): JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => $e->getMessage() ?: 'Unauthenticated.',
                ],
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    protected function renderAuthorizationException(Request $request, AuthorizationException $e): JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ],
            ], 403);
        }

        abort(403, $e->getMessage() ?: 'This action is unauthorized.');
    }

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    protected function renderTokenMismatch(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'SESSION_EXPIRED',
                    'message' => 'Sesi telah berakhir. Silakan login kembali.',
                ],
            ], 419);
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Sesi telah berakhir. Silakan login kembali.');
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                \Sentry\captureException($e);
            }
        });
    }
}
