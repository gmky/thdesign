<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);
        return Product::query()->paginate(perPage: $size, page: $page);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all(['name', 'description']);
        $featured_images = $request->file('featured_images');
        $banner = $request->file('banner');
        $thumbnail = $request->file('thumbnail');

        $featured_images_json = $this->uploadFiles($featured_images);
        $banner = $this->uploadFile($banner);
        $thumbnail = $this->uploadFile($thumbnail);

        $product = new Product($data);
        $product->featured_images = $featured_images_json;
        $product->thumbnail = $thumbnail;
        $product->banner = $banner;
        $product->save();
        return $product;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        return $product;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin())
            return response()->json(['message' => 'Unauthorized'], 401);

        Product::destroy($id);
        return response()->json(null);
    }

    private function uploadFiles($files)
    {
        $featured_images_json = [];
        foreach ($files as $file) {
            $filePath = $file->storeAs('/products', $file->hashName(), 'public');
            $featured_images_json[] = $filePath;
        }
        return json_encode($featured_images_json);
    }

    private function uploadFile($file)
    {
        return $file->storeAs('/products', $file->hashName(), 'public');
    }
}
