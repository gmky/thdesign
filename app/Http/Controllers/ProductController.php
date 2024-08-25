<?php

namespace App\Http\Controllers;

use App\Models\ImageSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);
        return Product::query()->with('author')->paginate(perPage: $size, page: $page);
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
    public function store(Request $request) : JsonResponse
    {
        if (!auth()->user()->canCreateProduct())
            return response()->json(['message' => 'Unauthorized'], 401);

        $banner = null;
        $thumbnail = null;

        try {
            DB::beginTransaction();
            $data = $request->all(['name', 'description', 'published', 'category_id', 'author_id', 'external_link']);
            $image_set_ids = $request->get('image_set', []);
            $banner = $request->file('banner');
            $thumbnail = $request->file('thumbnail');

            $banner = $this->uploadFile($banner);
            $thumbnail = $this->uploadFile($thumbnail);

            $product = new Product($data);
            $product->thumbnail = $thumbnail;
            $product->banner = $banner;
            $product->description = e($data['description']);
            $product->save();
            $image_set = ImageSet::whereNull('product_id')->whereIn('id', $image_set_ids)->get();
            $product->image_set()->saveMany($image_set);
            $product->load(['image_set', 'image_set.images']);
            DB::commit();
            return response()->json($product);
        } catch (\Exception $ex) {
            DB::rollBack();
            Storage::disk('public')->delete($banner);
            Storage::disk('public')->delete($thumbnail);
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);
        $product->load(['image_set', 'image_set.images', 'author']);
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
        if (!auth()->user()->canCreateProduct())
            return response()->json(['message' => 'Unauthorized'], 401);

        $product = Product::findOrFail($id);
        $old_banner = $product->banner;
        $old_thumbnail = $product->thumbnail;
        try {
            DB::beginTransaction();
            $data = request()->all(['name', 'description', 'published', 'category_id', 'author_id', 'external_link', 'author_id']);
            $image_set_ids = $request->get('image_set', []);
            $banner = $request->file('banner');
            $thumbnail = $request->file('thumbnail');
            if ($banner) {
                $product->banner = $this->uploadFile($banner);
            }
            if ($thumbnail) {
                $product->thumbnail = $this->uploadFile($thumbnail);
            }
            $product->update($data);
            $product->description = e($data['description']);
            $image_set = ImageSet::whereNull('product_id')->whereIn('id', $image_set_ids)->get();
            $product->image_set()->saveMany($image_set);
            $product->save();
            DB::commit();
            return response()->json($product);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['message' => $ex->getMessage()], 500);
        } finally {
            Storage::disk('public')->delete($old_banner);
            Storage::disk('public')->delete($old_thumbnail);
        }
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

    private function uploadFiles($files = [])
    {
        $featured_images_json = [];
        foreach ($files as $file) {
            $filePath = $file->storeAs('/products', time().'_'.$file->hashName(), 'public');
            $featured_images_json[] = $filePath;
        }
        return json_encode($featured_images_json);
    }

    private function uploadFile($file)
    {
        return $file->storeAs('/products', time().'_'.$file->hashName(), 'public');
    }
}
