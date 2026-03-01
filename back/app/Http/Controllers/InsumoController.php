<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    private function normalizeTipoInsumo($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $tipo = strtolower(trim((string)$value));
        return in_array($tipo, ['alimento', 'bebida', 'extra'], true) ? $tipo : null;
    }

    // GET /insumos
    public function index()
    {
        return Insumo::orderBy('id', 'desc')->get();
    }

    // POST /insumos
    public function store(Request $request)
    {
        $payload = $request->all();
        $payload['tipo_insumo'] = $this->normalizeTipoInsumo($request->input('tipo_insumo'));

        $insumo = Insumo::create($payload);
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
        $payload = $request->all();
        if ($request->has('tipo_insumo')) {
            $payload['tipo_insumo'] = $this->normalizeTipoInsumo($request->input('tipo_insumo'));
        }
        $insumo->update($payload);
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
