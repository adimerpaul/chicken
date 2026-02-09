<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        return Proveedor::orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
        ]);

        $nombre = trim($data['nombre']);
        if ($nombre === '') {
            return response()->json(['message' => 'Nombre inválido'], 422);
        }

        $proveedor = Proveedor::firstOrCreate(['nombre' => $nombre]);

        return $proveedor;
    }
}
