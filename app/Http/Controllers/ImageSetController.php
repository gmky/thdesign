<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\ImageSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageSetController extends Controller
{
    public function upload(Request $request)
    {
        $images = $request->file('images');
        Log::debug($images);
        $image_set = new ImageSet();
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
        return response()->json($image_set);
    }
}
