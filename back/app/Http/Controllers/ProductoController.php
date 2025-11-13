<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\InsumoProducto;
use App\Models\Insumo;
class ProductoController extends Controller
{
    // ================= INSUMOS DEL PRODUCTO =================

    // GET /productos/{product}/insumos
    public function insumosIndex(Producto $product)
    {
        return InsumoProducto::with('insumo')
            ->where('producto_id', $product->id)
            ->orderBy('id')
            ->get();
    }

    // POST /productos/{product}/insumos
    public function insumosStore(Request $request, Producto $product)
    {
        // validación simple
        $data = $request->validate([
            'insumo_id' => ['required', 'exists:insumos,id'],
            'cantidad'  => ['required', 'numeric'],
        ]);

        $data['producto_id'] = $product->id;

        $rel = InsumoProducto::create($data);

        // para que el front reciba el insumo embebido
        return $rel->load('insumo');
    }

    // PUT /productos/{product}/insumos/{insumoProducto}
    public function insumosUpdate(Request $request, Producto $product, InsumoProducto $insumoProducto)
    {
        // nos aseguramos que la relación pertenece a ese producto
        if ($insumoProducto->producto_id !== $product->id) {
            abort(404);
        }

        $data = $request->validate([
            'cantidad' => ['required', 'numeric'],
        ]);

        $insumoProducto->update($data);

        return $insumoProducto->load('insumo');
    }

    // DELETE /productos/{product}/insumos/{insumoProducto}
    public function insumosDestroy(Producto $product, InsumoProducto $insumoProducto)
    {
        if ($insumoProducto->producto_id !== $product->id) {
            abort(404);
        }

        $insumoProducto->delete();

        return response()->json(['message' => 'Insumo eliminado del producto']);
    }
    // GET /products  (lista con filtros básicos)
    public function index(Request $req)
    {
        return Producto::all();
    }

    // POST /products  (crear con subida opcional de imagen)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
//            'category_id' => ['nullable','exists:categories,id'],
            'categoria'    => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'price'       => ['required','numeric','min:0'],
            'unit'        => ['nullable','string','max:10'],
            'active'      => ['nullable','boolean'],
            'ord'         => ['nullable','integer','min:0'],
            'image'       => ['nullable','image','mimes:png,jpg,jpeg,webp','max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product = Producto::create($data);
        return response()->json($product->fresh(), 201);
    }

    // GET /products/{product}
    public function show(Producto $product) { return $product->load('category'); }

    // PUT /products/{product}
    public function update(Request $request, Producto $product)
    {
        $data = $request->validate([
            'name'        => ['sometimes','string','max:255'],
            'category_id' => ['sometimes','nullable','exists:categories,id'],
            'description' => ['sometimes','nullable','string'],
            'price'       => ['sometimes','numeric','min:0'],
            'unit'        => ['sometimes','string','max:10'],
            'active'      => ['sometimes','boolean'],
            'ord'         => ['sometimes','integer','min:0'],
            'image'       => ['sometimes','nullable','image','mimes:png,jpg,jpeg,webp','max:4096'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products','public');
        }

        $product->update($data);
        return $product->fresh();
    }

    // DELETE /products/{product}
    public function destroy(Producto $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return response()->json(['message'=>'Producto eliminado']);
    }
}
