<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun Anda tidak aktif. Hubungi administrator.'], 403);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Log
        AuditLog::log('login', "User {$user->name} login dari mobile app", User::class, $user->id);

        $token = $user->createToken($request->device_name ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'area' => $user->area,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'role' => $user->getRoleNames()->first(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        AuditLog::log('logout', "User {$user->name} logout dari mobile app");
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'phone' => $user->phone,
            'area' => $user->area,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'role' => $user->getRoleNames()->first(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'last_login_at' => $user->last_login_at,
        ]);
    }
}
