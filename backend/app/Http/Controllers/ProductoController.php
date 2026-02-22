<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    /**
     * Listar todos los productos
     */
    public function index(Request $request)
    {
        $query = Producto::query();

        // Filtro por estado
        if ($request->has('activo')) {
            $query->where('activo', $request->activo === 'true' || $request->activo === '1');
        } else {
            // Por defecto, solo productos activos
            $query->activos();
        }

        // Búsqueda por nombre o código
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_producto', 'like', "%{$search}%")
                  ->orWhere('codigo_producto', 'like', "%{$search}%");
            });
        }

        // Ordenar
        $orderBy = $request->order_by ?? 'nombre_producto';
        $orderDir = $request->order_dir ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        $productos = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }

    /**
     * Crear un nuevo producto
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_producto' => 'required|string|max:50|unique:productos',
            'nombre_producto' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $producto = Producto::create([
            'codigo_producto' => $request->codigo_producto,
            'nombre_producto' => $request->nombre_producto,
            'descripcion' => $request->descripcion,
            'precio_venta' => $request->precio_venta,
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $producto
        ], 201);
    }

    /**
     * Obtener un producto específico
     */
    public function show($id)
    {
        $producto = Producto::with(['ventas' => function($query) {
            $query->latest()->take(10);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $producto
        ]);
    }

    /**
     * Actualizar un producto
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'codigo_producto' => 'sometimes|string|max:50|unique:productos,codigo_producto,' . $id . ',id_producto',
            'nombre_producto' => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'sometimes|numeric|min:0',
            'activo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $producto->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => $producto
        ]);
    }

    /**
     * Desactivar un producto (no se elimina, solo se marca como inactivo)
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        $producto->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Producto desactivado exitosamente'
        ]);
    }

    /**
     * Activar un producto
     */
    public function activar($id)
    {
        $producto = Producto::findOrFail($id);

        $producto->update(['activo' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Producto activado exitosamente',
            'data' => $producto
        ]);
    }

    /**
     * Obtener productos más vendidos
     */
    public function masVendidos(Request $request)
    {
        $limite = $request->limite ?? 10;

        $productos = Producto::withCount('ventas')
                            ->activos()
                            ->orderBy('ventas_count', 'desc')
                            ->take($limite)
                            ->get();

        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }
}