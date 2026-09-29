<?php

namespace Modules\BackOffice\Services;

/** Who a system message goes to. Empty strings are treated as missing. */
class MessageRecipient
{
    public $name;
    public $email;
    public $mobile;
    public $cc;

    public function __construct($name, $email = null, $mobile = null, array $cc = [])
    {
        $this->name = trim((string) $name);
        $this->email = trim((string) $email) !== '' ? trim((string) $email) : null;
        $this->mobile = trim((string) $mobile) !== '' ? trim((string) $mobile) : null;
        $clean = [];
        foreach ($cc as $c) {
            $c = trim((string) $c);
            if ($c !== '' && strcasecmp($c, (string) $this->email) !== 0 && !in_array($c, $clean, true)) {
                $clean[] = $c;
            }
        }
        $this->cc = $clean;
    }
}
