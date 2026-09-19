<?php

namespace Modules\Maintenance\Exceptions;

class InvalidStateException extends \DomainException
{
    public function code(): string { return 'invalid_state'; }
}
