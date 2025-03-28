<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:usuarios.listar')->only('index');
        $this->middleware('check_permission:usuarios.ver')->only('show');
        $this->middleware('check_permission:usuarios.crear')->only(['create', 'store']);
        $this->middleware('check_permission:usuarios.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:usuarios.eliminar')->only('destroy');
    }
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }

        $users = $query->with('role')->orderByDesc('created_at')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role_id'  => 'required|exists:roles,id',
        ]);

        try {
            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role_id'  => $request->role_id,
            ]);

            Alert::success('Éxito', 'Usuario creado correctamente.');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Ocurrió un error al crear el usuario.');
            return redirect()->back()->withInput();
        }
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => "required|string|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:6|confirmed',
            'role_id'  => 'required|exists:roles,id',
        ]);

        try {
            $user->update([
                'name'     => $request->name,
                'email'    => $request->email,
                'role_id'  => $request->role_id,
                'password' => $request->filled('password')
                                ? Hash::make($request->password)
                                : $user->password,
            ]);

            Alert::success('Éxito', 'Usuario actualizado correctamente.');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Ocurrió un error al actualizar el usuario.');
            return redirect()->back()->withInput();
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            Alert::error('Error', 'No puedes eliminar tu propia cuenta.');
            return redirect()->route('users.index');
        }

        try {
            $user->delete();
            Alert::success('Eliminado', 'Usuario eliminado correctamente.');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudo eliminar el usuario.');
        }

        return redirect()->route('users.index');
    }
}
