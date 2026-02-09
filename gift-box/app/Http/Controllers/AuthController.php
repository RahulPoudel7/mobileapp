<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /**
     * ---------- WEB AUTH FOR ADMIN DASHBOARD ----------
     */

    // Show Blade login form for admin
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle web login using session guard
    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    // Optional web logout
    public function webLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * ---------- API AUTH (MOBILE / POSTMAN) ----------
     */

    // Register a new user (API)
    public function register(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->input('phone');
            if (! is_string($phone) && ! is_null($phone)) {
                $request->merge(['phone' => (string) $phone]);
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', PasswordRule::min(8)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ]);

        // Hash password
        $data['password'] = Hash::make($data['password']);

        // Create user
        $user = User::create($data);

        // Generate and send OTP
        $this->generateAndSendOtp($user->email, $user->name);

        // For development: return OTP in response (remove in production)
        $otpRecord = Otp::where('email', $user->email)->latest()->first();
        $otp = $otpRecord ? $otpRecord->otp : null;

        return response()->json([
            'success' => true,
            'message' => 'Account created! OTP has been sent to your email. Please verify to complete registration.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => 'Verify OTP to activate account',
                'otp' => $otp, // For development only - remove in production
            ],
        ], 201);
    }

    /**
     * Generate OTP and send to email
     */
    private function generateAndSendOtp(string $email, string $name): void
    {
        try {
            // Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Delete any existing OTP for this email
            Otp::where('email', $email)->delete();

            // Create new OTP (valid for 10 minutes)
            Otp::create([
                'email' => $email,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]);

            // Send OTP via email
            try {
                Mail::to($email)->send(new SendOtpMail($email, $otp, $name));
                \Log::info("OTP sent successfully to {$email}");
            } catch (\Exception $e) {
                \Log::error("Failed to send OTP to {$email} via email: " . $e->getMessage());
                // Email sending failed, but OTP is still stored
                // The user can still use the OTP if they check their email
            }
        } catch (\Exception $e) {
            \Log::error("Failed to create OTP for {$email}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify OTP and activate account (API)
     */
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        // Find OTP record
        $otpRecord = Otp::where('email', $data['email'])
            ->where('otp', $data['otp'])
            ->first();

        // Validate OTP exists
        if (! $otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
            ], 422);
        }

        // Check if OTP is expired
        if (! $otpRecord->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired',
            ], 422);
        }

        // Check max attempts
        if ($otpRecord->maxAttemptsExceeded()) {
            $otpRecord->delete();
            return response()->json([
                'success' => false,
                'message' => 'Maximum OTP attempts exceeded. Please register again.',
            ], 422);
        }

        // Mark OTP as verified
        $otpRecord->verify();

        // Update user as verified (optional: add 'email_verified_at')
        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $user->update(['email_verified_at' => now()]);
        }

        // Generate auth token
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully! You can now login.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'time' => now()->toDateTimeString(),
            ],
        ], 200);
    }

    /**
     * Resend OTP (API)
     */
    public function resendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // Generate and send new OTP
        $this->generateAndSendOtp($user->email, $user->name);

        return response()->json([
            'success' => true,
            'message' => 'OTP has been resent to your email',
            'data' => [
                'email' => $user->email,
            ],
        ], 200);
    }

    // API login: returns Sanctum token
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // API logout: revoke current token
    public function logout(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out'], 200);
    }

    // API: show profile
    public function profile(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json(['user' => $user], 200);
    }

    // API: revoke all tokens
    public function logoutAll(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)
            ->where('tokenable_type', get_class($user))
            ->delete();

        return response()->json(['message' => 'All tokens revoked'], 200);
    }

    // API: update default delivery address
    public function updateDefaultAddress(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'default_delivery_address' => ['required', 'string', 'max:255'],
            'default_delivery_lat' => ['required', 'numeric'],
            'default_delivery_lng' => ['required', 'numeric'],
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => [
                'default_delivery_address' => $user->default_delivery_address,
                'default_delivery_lat' => $user->default_delivery_lat,
                'default_delivery_lng' => $user->default_delivery_lng,
            ]
        ]);
    }

    // API: change password
    public function changePassword(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Verify old password
        if (!Hash::check($data['old_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password is incorrect'
            ], 422);
        }

        // Update to new password (only password field)
        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    // API: update profile (name and phone)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\d{10}$/', 'digits:10'],
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    // API: delete account
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        // Get user ID before deletion
        $userId = $user->id;

        // Delete all user tokens
        \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $userId)
            ->where('tokenable_type', get_class($user))
            ->delete();

        // Delete the user account
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    // API: request password reset
    public function requestPasswordReset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 422);
        }

        // Generate 6-digit OTP for password reset
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing OTP for this email
        Otp::where('email', $data['email'])->delete();

        // Create new OTP (valid for 15 minutes)
        Otp::create([
            'email' => $data['email'],
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send OTP via email
        try {
            Mail::to($data['email'])->send(new SendOtpMail($data['email'], $otp, $user->name));
            \Log::info("Password reset OTP sent to {$data['email']}");
        } catch (\Exception $e) {
            \Log::error("Failed to send password reset OTP to {$data['email']}: " . $e->getMessage());
        }

        // For development: return OTP in response (remove in production)
        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email',
            'data' => [
                'email' => $data['email'],
                'otp' => $otp, // For development only - remove in production
            ]
        ]);
    }

    // API: verify OTP and reset password
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', PasswordRule::min(8)],
        ]);

        // Find OTP record
        $otpRecord = Otp::where('email', $data['email'])
            ->where('otp', $data['otp'])
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
        }

        // Check if OTP is expired
        if (!$otpRecord->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired'
            ], 422);
        }

        // Find user and update password
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 422);
        }

        // Update password
        $user->password = Hash::make($data['password']);
        $user->save();

        // Delete used OTP
        $otpRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. Please login with your new password.'
        ]);
    }
}
