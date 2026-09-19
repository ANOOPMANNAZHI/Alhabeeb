<?php

namespace Modules\MobileApi\Exceptions;

class DeviceException extends \RuntimeException
{
    public const NOT_REGISTERED = 'device_not_registered';
    public const BLOCKED = 'device_blocked';

    public function __construct(private readonly string $code_)
    {
        parent::__construct(match ($code_) {
            self::BLOCKED => 'This device has been blocked. Please contact the administrator.',
            default => 'This device is not registered. Please contact the administrator.',
        });
    }

    public function code(): string
    {
        return $this->code_;
    }
}
