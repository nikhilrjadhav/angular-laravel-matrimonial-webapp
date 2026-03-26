<?php

namespace App\Exceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\ApiErrorLog;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception): Response
    {
        if (!$request->is('api/*')) {
            return parent::render($request, $exception);
        }

        $requestId = $request->attributes->get('request_id');

        // 401 - Unauthenticated
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => false,
                'error_code' => 'UNAUTHENTICATED',
                'message' => 'auth.unauthenticated',
                'errors' => [],
                'meta' => ['request_id' => $requestId]
            ], 401);
        }

        // 403 - Forbidden
        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'status' => false,
                'error_code' => 'FORBIDDEN',
                'message' => 'auth.forbidden',
                'errors' => [],
                'meta' => ['request_id' => $requestId]
            ], 403);
        }

        // 404 - Model Not Found
        if (
            $exception instanceof ModelNotFoundException ||
            $exception instanceof NotFoundHttpException
        ) {

            return response()->json([
                'status' => false,
                'error_code' => 'NOT_FOUND',
                'message' => 'general.not_found',
                'errors' => [],
                'meta' => ['request_id' => $requestId]
            ], 404);
        }

        // Database error (log it)
        if ($exception instanceof QueryException) {

            $this->logCriticalError($exception, $request, $requestId);

            return response()->json([
                'status' => false,
                'error_code' => 'INTERNAL_ERROR',
                'message' => 'general.something_went_wrong',
                'errors' => [],
                'meta' => ['request_id' => $requestId]
            ], 500);
        }

        // Fallback - unexpected error
        $this->logCriticalError($exception, $request, $requestId);

        return response()->json([
            'status' => false,
            'error_code' => 'INTERNAL_ERROR',
            'message' => 'general.something_went_wrong',
            'errors' => [],
            'meta' => ['request_id' => $requestId]
        ], 500);
    }

    protected function logCriticalError(Throwable $exception, $request, ?string $requestId): void
    {
        $data = [
            'request_id' => $requestId,
            'user_id' => optional($request->user())->id,
            'ip_address' => $request->ip(),
            'route' => $request->path(),
            'method' => $request->method(),
            'error_message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'payload' => json_encode(
                $request->except(['password', 'password_confirmation'])
            )
        ];

        // File log
        \Log::error('API Critical Error', $data);

        // DB log
        ApiErrorLog::create($data);
    }
}
