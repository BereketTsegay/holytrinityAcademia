<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInvitation;
use Carbon\Carbon;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:student,teacher',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        // Assign role
        $user->assignRole($request->role);

        // Generate student ID or employee ID
        if ($request->role === 'student') {
            $user->update([
                'student_id' => 'STU' . str_pad($user->id, 6, '0', STR_PAD_LEFT)
            ]);
        } else {
            $user->update([
                'employee_id' => 'EMP' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'joining_date' => now(),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
            'message' => 'User registered successfully'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not active. Please contact administrator.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }




    public function inviteUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'role' => 'required|in:student,teacher',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        // Create user with temporary password
        $tempPassword = Str::random(12);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'status' => 'active',
            'email_verified_at' => null,
        ]);

        // Assign role
        $user->assignRole($request->role);

        // Generate IDs
        if ($request->role === 'student') {
            $user->update([
                'student_id' => 'STU' . str_pad($user->id, 6, '0', STR_PAD_LEFT)
            ]);
        } else {
            $user->update([
                'employee_id' => 'EMP' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'joining_date' => now(),
            ]);
        }

        // Send invitation email
        try {
            Mail::to($user->email)->send(new UserInvitation($user, $tempPassword));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Invitation email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'User invited successfully. Invitation email sent.',
            'user' => $user
        ], 201);
    }

    public function completeRegistration(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $request->user();

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Registration completed successfully',
            'user' => $user->load('roles')
        ]);
    }
public function forgotPassword(Request $request)
{
    $request->validate(['email' => 'required|email']);

    // Check if user exists
    $user = User::where('email', $request->email)->first();
    if (!$user) {
        return response()->json([
            'message' => 'If the email exists in our system, a password reset link has been sent.'
        ]);
    }

    // Generate reset token
    $status = Password::sendResetLink(
        $request->only('email')
    );

    if ($status === Password::RESET_LINK_SENT) {
        // Log the reset attempt
        \Log::info("Password reset link sent to: {$request->email}");

        return response()->json([
            'message' => 'If the email exists in our system, a password reset link has been sent.'
        ]);
    }

    return response()->json([
        'message' => 'Unable to send reset link. Please try again later.'
    ], 400);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));

            // Log the password reset
            \Log::info("Password reset successful for user: {$user->email}");
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return response()->json([
            'message' => 'Password has been reset successfully.'
        ]);
    }

    return response()->json([
        'message' => 'Invalid or expired reset token.'
    ], 400);
}

public function validateResetToken(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email'
    ]);

    // Check if token is valid
    $tokens = DB::table('password_reset_tokens')->get();
    $tokenExists = $tokens->contains(function ($token) use ($request) {
        return Hash::check($request->token, $token->token) &&
               $token->email === $request->email;
    });

    if (!$tokenExists) {
        return response()->json([
            'valid' => false,
            'message' => 'Invalid or expired reset token.'
        ], 400);
    }

    // Check if token is expired (default is 60 minutes)
    $tokenRecord = $tokens->first(function ($token) use ($request) {
        return Hash::check($request->token, $token->token) &&
               $token->email === $request->email;
    });

    $createdAt = Carbon::parse($tokenRecord->created_at);
    $expiresAt = $createdAt->addMinutes(config('auth.passwords.users.expire', 60));

    if ($expiresAt->isPast()) {
        return response()->json([
            'valid' => false,
            'message' => 'Reset token has expired.'
        ], 400);
    }

    return response()->json([
        'valid' => true,
        'message' => 'Token is valid.'
    ]);
}
}
