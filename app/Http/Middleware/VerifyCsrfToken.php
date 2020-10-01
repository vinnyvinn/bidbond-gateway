<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/safaricom/c2b/validation/callback/*',
        '/safaricom/c2b/confirmation/callback/*',
        '/safaricom/trx-status/timeout/callback/*',
        '/safaricom/trx-status/confirmation/callback/*',
        '/safaricom/stk/confirmation/callback/*',
        '/agent/restore-limit',
        '/agent/usage',
        '/hfcgroup/c2b/callback'
    ];
}
