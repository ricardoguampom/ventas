<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:proveedores.listar')->only('index');
        $this->middleware('check_permission:proveedores.ver')->only('show');
        $this->middleware('check_permission:proveedores.crear')->only(['create', 'store']);
        $this->middleware('check_permission:proveedores.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:proveedores.eliminar')->only('destroy');
    }

    public function index()
    {
        $query = Person::where('type', 'provider');

        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }
    
        $providers = $query->orderBy('name')->paginate(5);
        return view('providers.index', compact('providers'));
    }

    public function show(Person $provider)
    {
        abort_unless($provider->type === 'provider', 404);
        return view('providers.show', compact('provider'));
    }

    public function create()
    {
        return view('providers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'nullable|in:CI,NIT,RUC',
            'document_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Person::create($request->all() + ['type' => 'provider']);

        Alert::success('Éxito', 'Proveedor registrado correctamente.');
        return redirect()->route('providers.index');
    }

    public function edit(Person $provider)
    {
        abort_unless($provider->type === 'provider', 404);
        return view('providers.edit', compact('provider'));
    }

    public function update(Request $request, Person $provider)
    {
        abort_unless($provider->type === 'provider', 404);

        $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'nullable|in:CI,NIT,RUC',
            'document_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $provider->update($request->all());

        Alert::success('Éxito', 'Proveedor actualizado correctamente.');
        return redirect()->route('providers.index');
    }

    public function destroy(Person $provider)
    {
        abort_unless($provider->type === 'provider', 404);

        $provider->delete();
        Alert::success('Eliminado', 'Proveedor eliminado correctamente.');
        return redirect()->route('providers.index');
    }
}
