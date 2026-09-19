<?php

namespace Modules\Maintenance\Exceptions;

class NotAssignedException extends \DomainException
{
    public function code(): string { return 'not_assigned'; }
}
