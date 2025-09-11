<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($perPage = (int) $request->query('per_page')) {
            $paginator = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'code' => 200,
                'data' => [
                    'users' => UserResource::collection($paginator->items()),
                    'pagination' => [
                        'total' => $paginator->total(),
                        'per_page' => $paginator->perPage(),
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
            ]);
        }

        $users = $query->get();
        return response()->json(['success' => true, 'code' => 200, 'data' => UserResource::collection($users)]);
    }

    public function store(UserStoreRequest $request)
    {
        $user = User::create($request->only(['name', 'email', 'password']));
        return response()->json(['success' => true, 'code' => 201, 'data' => new UserResource($user)], 201);
    }

    public function show(User $user)
    {
        return response()->json(['success' => true, 'code' => 200, 'data' => new UserResource($user)]);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->only(['name', 'email']);
        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }
        $user->update($data);
        return response()->json(['success' => true, 'code' => 200, 'data' => new UserResource($user)]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true, 'code' => 200, 'data' => null]);
    }
}
