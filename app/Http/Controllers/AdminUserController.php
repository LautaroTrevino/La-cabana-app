<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => 'required|in:admin,administrativo,usuario',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,administrativo,usuario']);

        // No permitir que el admin se quite su propio rol
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'No podés cambiar tu propio rol de administrador.');
        }

        $user->update(['role' => $request->role]);
        return back()->with('success', 'Rol de ' . $user->name . ' actualizado a ' . $request->role . '.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No podés eliminar tu propia cuenta.');
        }

        $user->delete();
        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Verifica la contraseña del admin (usado por usuarios "administrativo"
     * para autorizar eliminaciones sensibles).
     */
    public function verifyAdminPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $admin = User::where('role', 'admin')->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false], 403);
    }
}
