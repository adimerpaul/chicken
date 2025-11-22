<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    // GET /insumos
    public function index()
    {
        return Insumo::orderBy('id', 'desc')->get();
    }

    // POST /insumos
    public function store(Request $request)
    {
        // sin validaciones, directo
        $insumo = Insumo::create($request->all());
        return response()->json($insumo, 201);
    }

    // GET /insumos/{id}
    public function show($id)
    {
        return Insumo::findOrFail($id);
    }

    // PUT /insumos/{id}
    public function update(Request $request, $id)
    {
        $stock = $request->input('stock');
        if ($stock == '') {
            return false;
        }
        $insumo = Insumo::findOrFail($id);
        $insumo->update($request->all());
        return $insumo;
    }

    // DELETE /insumos/{id}
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->delete();
        return response()->json(['message' => 'Insumo eliminado']);
    }
}
