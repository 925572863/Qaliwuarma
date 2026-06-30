<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|max:255|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ], [
            'name.required'         => 'El nombre es obligatorio.',
            'email.required'        => 'El correo es obligatorio.',
            'email.unique'          => 'Ya existe un usuario con ese correo.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.unique'       => 'Ya existe un usuario con ese correo.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'     => 'La contraseña es obligatoria.',
            'password.min'          => 'Debe tener al menos 8 caracteres.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('users.index')
            ->with('success', "Contraseña de {$user->name} restablecida exitosamente.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado.');
    }
}
