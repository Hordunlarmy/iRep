<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckUserActivated
{
    public function handle(Request $request, Closure $next)
    {
        $email = $request->input('email');
        $account = null;

        if ($email) {
            $account = DB::table('accounts')->where('email', $email)->first();
        } else {
            $account = Auth::user();
        }

        if (!$account) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if (!$account->email_verified) {
            return response()->json(['message' => 'Account is not activated.'], 403);
        }

        return $next($request);
    }
}
