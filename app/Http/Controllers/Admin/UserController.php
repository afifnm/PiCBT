<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index');
    }

    public function json(Request $request): JsonResponse
    {
        $q = User::query()
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%$s%")
                  ->orWhere('username', 'like', "%$s%")
            )
            ->when($request->role, fn ($q, $r) => $q->where('role', $r))
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $q->items(),
            'meta' => [
                'total'        => $q->total(),
                'current_page' => $q->currentPage(),
                'last_page'    => $q->lastPage(),
                'from'         => $q->firstItem(),
                'to'           => $q->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'username'  => ['required', 'string', 'max:50', 'unique:users,username'],
            'role'      => ['required', 'in:admin,guru'],
            'password'  => ['required', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'username'  => ['required', 'string', 'max:50', "unique:users,username,{$user->id}"],
            'role'      => ['required', 'in:admin,guru'],
            'is_active' => ['boolean'],
        ]);

        $user->update($data);

        return response()->json($user);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password berhasil direset.']);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User dihapus.']);
    }
}
