<?php

namespace App\Exceptions;

Use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Carbon\Exceptions\InvalidFormatException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // Si es una solicitud de API, sanitizar la respuesta para evitar errores UTF-8
        if ($request->expectsJson()) {
            return $this->renderJsonException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render excepción como respuesta JSON sanitizada
     */
    protected function renderJsonException($request, Throwable $exception)
    {
        $status = 500;
        $sqlState = null;

        // Manejo específico para excepciones de Carbon
        if ($exception instanceof InvalidFormatException) {
            $status = 400; // Bad Request para errores de formato de fecha
            $message = 'Formato de fecha inválido';
        } else {
            // Obtener código de estado HTTP apropiado
            if (method_exists($exception, 'getStatusCode')) {
                $status = $exception->getStatusCode();
            } elseif ($exception instanceof \PDOException
                || $exception instanceof \Illuminate\Database\QueryException) {
                // PDOException::getCode() devuelve el SQLSTATE (ej. "22007"), no un código HTTP
                $sqlState = (string) $exception->getCode();
                $status = 500;
            } elseif (method_exists($exception, 'getCode')) {
                $code = (int) $exception->getCode();
                if ($code >= 100 && $code <= 599) {
                    $status = $code;
                }
            }
            $message = 'Error interno del servidor';
        }

        // Sanitizar mensaje de error
        $errorMessage = $this->sanitizeUtf8($exception->getMessage());

        $response = [
            'success' => false,
            'message' => $message,
            'error' => $errorMessage,
        ];

        if ($sqlState !== null) {
            $response['sqlstate'] = $sqlState;
        }

        // En desarrollo, agregar más información
        if (config('app.debug')) {
            $response['exception'] = get_class($exception);
            $response['file'] = $exception->getFile();
            $response['line'] = $exception->getLine();
        }

        return response()->json($response, $status, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * Sanitizar string para UTF-8 válido
     */
    protected function sanitizeUtf8($text)
    {
        if (!is_string($text)) {
            return $text;
        }

        // Detectar si es contenido binario (como certificados)
        if ($this->isBinaryData($text)) {
            return 'Datos binarios detectados - información no mostrable';
        }

        // Convertir a UTF-8 válido
        $sanitized = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Remover caracteres de control problemáticos excepto espacios y saltos de línea
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sanitized);

        // Límite de longitud para evitar respuestas demasiado largas
        if (strlen($sanitized) > 1000) {
            $sanitized = substr($sanitized, 0, 1000) . '... (truncado)';
        }

        return $sanitized;
    }

    /**
     * Detectar si el contenido es binario
     */
    protected function isBinaryData($text)
    {
        // Buscar patrones típicos de datos binarios
        if (preg_match('/[\x00-\x08\x0E-\x1F\x7F-\xFF]{10,}/', $text)) {
            return true;
        }

        // Buscar secuencias típicas de certificados
        if (strpos($text, '*?H??') !== false ||
            strpos($text, '\r\n') !== false && preg_match('/[^\x20-\x7E\r\n\t]{20,}/', $text)) {
            return true;
        }

        return false;
    }
}
