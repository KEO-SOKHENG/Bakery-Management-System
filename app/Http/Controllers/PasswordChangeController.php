<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    /**
     * Show the mandatory change password screen.
     */
    public function show()
    {
        $user = Auth::user();
        return view('auth.change-password', compact('user'));
    }

    /**
     * Update the user password and remove the mandatory change flag.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'Your new password cannot be the same as your temporary password.',
            'current_password.current_password' => 'The provided current/temporary password does not match our records.',
        ]);

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        AuditLogService::log(
            'password_changed',
            "User '{$user->username}' successfully updated their temporary password",
            $user,
            ['ip' => $request->ip()]
        );

        $targetRoute = match (strtolower($user->role)) {
            'admin' => 'admin.dashboard',
            'manager' => 'manager.dashboard',
            'cashier' => 'cashier.dashboard',
            'baker' => 'admin.production',
            default => 'login',
        };

        return redirect()->route($targetRoute)->with('success', 'Your password has been changed successfully!');
    }
}
