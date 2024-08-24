<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\ImageSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageSetController extends Controller
{
    public function upload(Request $request)
    {
        if (!auth()->user()->canCreateProduct())
            return response()->json(['message' => 'Unauthorized'], 401);
        try {
            DB::beginTransaction();
            $images = $request->file('images');
            $image_set = new ImageSet();
            $image_set->display_order = $request->only('display_order');
            $image_set->save();
            foreach ($images as $image) {
                Log::info('Save image '.$image->getClientOriginalName());
                $filePath = $image->storeAs('/products', $image->hashName(), 'public');
                $savedImage = new Image();
                $savedImage->name = $image->getClientOriginalName();
                $savedImage->path = $filePath;
                $image_set->images()->save($savedImage);
            }
            $image_set->load('images');
            DB::commit();
            return response()->json($image_set);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);
        return ImageSet::with(['images'])->paginate(perPage: $size, page: $page);
    }

    public function destroy($id)
    {
        if (!auth()->user()->canCreateProduct())
            return response()->json(['message' => 'Unauthorized'], 401);

        try {
            DB::beginTransaction();
            $image_set = ImageSet::find($id);
            $images = $image_set->images;
            foreach ($images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
            DB::commit();
            return response()->json()->setStatusCode(200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }
}
