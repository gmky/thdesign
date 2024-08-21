<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index()
    {
        $page = request()->query('page', 1);
        $size = request()->query('size', 10);

        return Image::query()->paginate(perPage: $size, page: $page);
    }

    public function destroy($id)
    {
        if (!auth()->user()->canCreateProduct())
            return response()->json(['message' => 'Unauthorized'], 401);
        $image = Image::query()->findOrFail($id);
        Storage::disk('public')->delete($image->path);
        $image->delete();
        return response()->json();
    }
}
