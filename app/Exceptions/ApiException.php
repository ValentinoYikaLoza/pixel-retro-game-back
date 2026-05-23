<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción de dominio. La lanza la capa de negocio (Services) cuando una regla
 * no se cumple. El Handler la convierte en el envelope estándar de error con el
 * código HTTP correcto.
 */
class ApiException extends Exception
{
    public function __construct(string $message, public readonly int $status = 400)
    {
        parent::__construct($message);
    }

    public static function badRequest(string $message): self
    {
        return new self($message, 400);
    }

    public static function forbidden(string $message): self
    {
        return new self($message, 403);
    }

    public static function notFound(string $message): self
    {
        return new self($message, 404);
    }

    public static function unprocessable(string $message): self
    {
        return new self($message, 422);
    }
}
