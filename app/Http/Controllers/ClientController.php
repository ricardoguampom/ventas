<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:clientes.listar')->only('index');
        $this->middleware('check_permission:clientes.ver')->only('show');
        $this->middleware('check_permission:clientes.crear')->only(['create', 'store']);
        $this->middleware('check_permission:clientes.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:clientes.eliminar')->only('destroy');
    }

    public function index()
    {
        $query = Person::where('type', 'client');

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

        $clients = $query->orderBy('name')->paginate(5);
        return view('clients.index', compact('clients'));
    }

    public function show(Person $client)
    {
        return view('clients.show', compact('client'));
    }

    public function create()
    {
        return view('clients.create');
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

        Person::create($request->all() + ['type' => 'client']);

        Alert::success('Éxito', 'Cliente registrado correctamente.');
        return redirect()->route('clients.index');
    }

    public function edit(Person $client)
    {
        abort_unless($client->type === 'client', 404);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Person $client)
    {
        abort_unless($client->type === 'client', 404);

        $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'nullable|in:CI,NIT,RUC',
            'document_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $client->update($request->all());

        Alert::success('Éxito', 'Cliente actualizado correctamente.');
        return redirect()->route('clients.index');
    }

    public function destroy(Person $client)
    {
        abort_unless($client->type === 'client', 404);

        $client->delete();
        Alert::success('Eliminado', 'Cliente eliminado correctamente.');
        return redirect()->route('clients.index');
    }
}
