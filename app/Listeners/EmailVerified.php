<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EmailVerified
{
    public function handle(Verified $event)
    {
        session()->flash('success', '邮箱验证成功^_^');
    }
}
