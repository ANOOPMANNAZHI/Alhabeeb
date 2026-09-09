<?php

namespace Modules\BackOffice\Exceptions;

/**
 * Raised by LandlordInvoiceV2AxPoster when an invoice cannot be posted to AX.
 * The message is safe to flash to the user.
 */
class AxPostingException extends \RuntimeException
{
}
