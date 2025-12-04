<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    // GET /almacenes
    public function index()
    {
        return Almacen::orderBy('id', 'desc')->get();
    }

    // POST /almacenes
    public function store(Request $request)
    {
        // igual que insumos: sin validaciones estrictas
        $almacen = Almacen::create($request->all());
        return response()->json($almacen, 201);
    }

    // GET /almacenes/{id}
    public function show($id)
    {
        return Almacen::findOrFail($id);
    }

    // PUT /almacenes/{id}
    public function update(Request $request, $id)
    {
        // copio el comportamiento raro que ya tienes en InsumoController
        $stock = $request->input('stock');
        if ($stock == '') {
            return false;
        }

        $almacen = Almacen::findOrFail($id);
        $almacen->update($request->all());

        return $almacen;
    }

    // DELETE /almacenes/{id}
    public function destroy($id)
    {
        $almacen = Almacen::findOrFail($id);
        $almacen->delete();
        return response()->json(['message' => 'Almacén eliminado']);
    }
}
