<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginApiRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    public function store(LoginApiRequest $request): JsonResponse
    {
        $login = $request->string('login')->toString();

        $user = User::query()
            ->where(fn ($query) => $query
                ->where('username', $login)
                ->orWhere('email', $login))
            ->where('active', true)
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json(['message' => 'Credencials incorrectes.'], 422);
        }

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'token' => $user->createToken($request->input('device_name', 'api-client'))->plainTextToken,
            'user' => $user,
        ]);
    }
}
