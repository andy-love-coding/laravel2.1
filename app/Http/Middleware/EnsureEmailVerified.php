<?php

namespace App\Http\Middleware;

use Closure;

class EnsureEmailVerified
{
    public function handle($request, Closure $next)
    {
        // 三个判断：
        // 1. 如果用户已经登录
        // 2. 并且还未认证 Email
        // 3. 并且访问的不是 email 验证相关 URL 或者退出的 URL。
        if ($request->user() && 
            ! $request->user()->hasVerifiedEmail() &&
            ! $request->is('email/*', 'logout')) {

                // 根据客户端返回对应的内容
                return $request->expectsJson()
                            ? abort(403, 'Your email address is not verified.') // 前请求是ajax返回的json，就 abort
                            : redirect()->route('verification.notice');// 否则就跳转“提示尚未验证”的通知网页
            }
        return $next($request);
    }
}
