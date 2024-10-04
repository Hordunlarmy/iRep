<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckUserVerified
{
    public function handle(Request $request, Closure $next)
    {
        $email = $request->input('email');

        if (!$email) {
            $account = auth()->user();
        }

        $account = DB::table('accounts')->where('email', $email)->first();

        if (!$account || !$account->email_verified) {
            return response()->json(['message' => 'Account is not verified.'], 403);
        }

        return $next($request);
    }
}
