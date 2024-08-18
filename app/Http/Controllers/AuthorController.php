<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);
        return Author::query()->paginate(perPage: $size, page: $page);
    }

    public function store(Request $request)
    {
        $data = $request->all(['name', 'email']);
        $avatar = $request->file('avatar');
        $avatar_path = '';
        try {
            $author = new Author($data);
            $avatar_path = $avatar->storeAs('/avatar', $avatar->hashName(), 'public');
            $author->avatar = $avatar_path;
            $author->save();
            return response()->json($author);
        } catch (\Exception $exception) {
            Log::error("Unable to create author: " . $exception->getMessage());
            Storage::disk('public')->delete($avatar_path);
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $author = Author::query()->findOrFail($id);
        return response()->json($author);
    }
}
