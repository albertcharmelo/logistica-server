<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Http\Requests\ResetPasswordRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // assign roles if provided
        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $response = [
            'success' => true,
            'code' => 201,
            'data' => [
                'token' => $token,
                'type' => 'Bearer',
                'user' => $this->transformUser($user->fresh()),
            ],
        ];

        return response()->json($response, 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json([
                'status' => 401,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'token' => $token,
                'type' => 'Bearer',
                'user' => $this->transformUser($user->fresh()),
            ],
        ], 200);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        // Token is intentionally ignored per requirements
        $user = User::where('email', $data['email'])->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'message' => 'Contraseña actualizada correctamente.',
            ],
        ]);
    }

    protected function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toDateTimeString(),
            'created_at' => $user->created_at?->toDateTimeString(),
            'updated_at' => $user->updated_at?->toDateTimeString(),
            'roles' => $user->getRoleNames()->toArray(),
            'permisos' => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
    }
}
