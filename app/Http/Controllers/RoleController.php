<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:roles.listar')->only('index');
        $this->middleware('check_permission:roles.ver')->only('show');
        $this->middleware('check_permission:roles.crear')->only(['create', 'store']);
        $this->middleware('check_permission:roles.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:roles.eliminar')->only('destroy');
        $this->middleware('check_permission:roles.verUsuarios')->only('showUsers');
    }

    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->when($request->filter, fn($query, $filter) =>
                $query->where('name', 'like', "%$filter%")
            )
            ->paginate(10);

        return view('roles.index', compact('roles'));
    }


    public function show(Role $role)
    {
        $role->load('permissions');
        return view('roles.show', compact('role'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get(); // ✅ colección plana
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role = Role::create(['name' => $request->name]);

            $role->permissions()->attach($request->permissions ?? []);

            Alert::success('Éxito', 'Rol creado correctamente.');
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudo crear el rol.');
            return redirect()->back()->withInput();
        }
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get(); // ✅ colección plana
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => "required|string|max:255|unique:roles,name,{$role->id}",
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role->update(['name' => $request->name]);
            $role->permissions()->sync($request->permissions ?? []);

            Alert::success('Éxito', 'Rol actualizado correctamente.');
            return redirect()->route('roles.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudo actualizar el rol.');
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            Alert::error('Error', 'No se puede eliminar un rol que tiene usuarios asignados.');
            return redirect()->route('roles.index');
        }

        $role->permissions()->detach();
        $role->delete();

        Alert::success('Eliminado', 'Rol eliminado correctamente.');
        return redirect()->route('roles.index');
    }

    public function showUsers(Role $role)
    {
        $users = $role->users()->paginate(10);
        return view('roles.users', compact('role', 'users'));
    }
}
