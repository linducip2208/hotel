<?php

namespace App\Services\Fo;

use RuntimeException;

/** Domain-level validation failure → maps to friendly validation error, never a 500. */
class ReservationValidationException extends RuntimeException {}
