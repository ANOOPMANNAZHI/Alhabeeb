<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            session()->flash('expiredSession', 'null');
            return redirect()
                ->back()
                ->withInput($request->except('_token'));
        }

        return parent::render($request, $e);
    }
}
