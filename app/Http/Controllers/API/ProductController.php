<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductResource::collection(Product::orderBy('updated_at', 'DESC')->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $product = $request->validated();

        if($request->hasFile('photo') && $request->photo->isValid()) {
            $product['photo'] = $request->photo->store("products");
        }

        Product::create($product);

        return;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('photo') && $request->photo->isValid()) {

            if (Storage::exists($product->photo)) {
                Storage::delete($product->photo);
            }

            $data['photo'] = $request->photo->store("products");
        }

        $product->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if (Storage::exists($product->photo)) {
            Storage::delete($product->photo);
        }

        $product->delete();

        return response(null, 204);
    }

    public function search(Request $request)
    {
        $products = Product::where('name', 'LIKE', "%{$request->keyword}%")
        ->latest()
        ->paginate();

        return ProductResource::collection($products);
    }
}
