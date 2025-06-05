<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    protected $proxies = '*'; // Or an array of proxy IPs

    protected function proxies(): array
    {
        return is_array($this->proxies) ? $this->proxies : [$this->proxies];
    }
}
