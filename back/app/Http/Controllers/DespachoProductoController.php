<?php

namespace App\Http\Controllers;

use App\Models\DespachoProducto;
use Illuminate\Http\Request;

class DespachoProductoController extends Controller
{
    // GET /despacho-productos
    public function index()
    {
        return DespachoProducto::orderBy('nombre')->get();
    }

    // POST /despacho-productos
    public function store(Request $request)
    {
        // creación rápida: también puede quien recibe; editar/eliminar solo el admin
        abort_unless(
            $request->user()->can('Despachos') || $request->user()->can('Despachos admin'),
            403,
            'No tiene permiso para crear productos de despacho.'
        );

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad' => 'required|string|max:20',
            'precio' => 'nullable|numeric|min:0',
            'activo' => 'boolean',
        ]);

        return DespachoProducto::create($data);
    }

    // PUT /despacho-productos/{id}
    public function update(Request $request, $id)
    {
        $this->soloAdmin($request);

        $producto = DespachoProducto::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad' => 'required|string|max:20',
            'precio' => 'nullable|numeric|min:0',
            'activo' => 'boolean',
        ]);
        $producto->update($data);

        return $producto;
    }

    // DELETE /despacho-productos/{id}
    public function destroy(Request $request, $id)
    {
        $this->soloAdmin($request);

        DespachoProducto::findOrFail($id)->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }

    private function soloAdmin(Request $request)
    {
        abort_unless($request->user()->can('Despachos admin'), 403, 'No tiene permiso para gestionar productos de despacho.');
    }
}
