<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->role->slug === 'super-admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role->slug === 'client') {
            return redirect()->route('client.dashboard');
        }

        if ($user->role->slug === 'marketing') {
            return redirect()->route('marketing.dashboard');
        }
        
        if ($user->role->slug === 'teknisi') {
            return redirect()->route('teknisi.dashboard');
        }

        return redirect('/');
    }
}