<?php

namespace App\Services\Fo;

/** Folio has outstanding balance; checkout requires settlement or manager override. */
class OutstandingBalanceException extends ReservationValidationException
{
    public function __construct(public readonly float $outstanding)
    {
        parent::__construct(sprintf('Folio masih memiliki saldo outstanding Rp %s. Lakukan settlement terlebih dahulu.', number_format($outstanding, 0, ',', '.')));
    }
}
