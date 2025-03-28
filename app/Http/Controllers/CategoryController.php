<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('check_permission:categorias.listar')->only('index');
        $this->middleware('check_permission:categorias.crear')->only(['create', 'store']);
        $this->middleware('check_permission:categorias.editar')->only(['edit', 'update']);
        $this->middleware('check_permission:categorias.eliminar')->only('destroy');
    }
    
    
    public function index(Request $request)
    {
        $query = Category::query();
    
        // 🔎 Filtro por nombre o descripción
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
    
        // ✅ Filtro por estado (activo/inactivo)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        $categories = $query->orderBy('created_at', 'desc')->paginate(5);
        return view('categories.index', compact('categories'));
    }
    

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        Category::create($request->all());

        Alert::success('Éxito', '¡Categoría creada con éxito!');
        return redirect()->route('categories.index');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        $category->update($request->all());

        Alert::success('Éxito', '¡Categoría actualizada con éxito!');
        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
            Alert::success('Eliminado', '¡Categoría eliminada con éxito!');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se puede eliminar esta categoría porque está en uso.');
        }

        return redirect()->route('categories.index');
    }
}
