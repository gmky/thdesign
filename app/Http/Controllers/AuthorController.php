<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (!auth()->user()->isAdmin())
            return response()->json(['message' => 'Unauthorized'], 401);

        $data = $request->all(['name', 'email', 'job_title', 'tags']);
        $avatar = $request->file('avatar');
        $avatar_path = '';
        try {
            $author = new Author($data);
            if ($avatar && $avatar->isValid()) {
                $avatar_path = $avatar->storeAs('/avatar', $avatar->hashName(), 'public');
                $author->avatar = $avatar_path;
            }
            $author->save();
            return response()->json($author);
        } catch (\Exception $exception) {
            Log::error("Unable to create author: " . $exception->getMessage());
            Storage::disk('public')->delete($avatar_path);
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        if (!auth()->user()->isAdmin())
            return response()->json(['message' => 'Unauthorized'], 401);
        $avatar_path = null;
        try {
            DB::beginTransaction();
            $data = $request->all(['name', 'email', 'tags', 'job_title']);
            $name_existed = Author::query()->where('id','!=', $id)->where('name', $data['name'])->count();
            if ($name_existed) {
                return response()->json(['message' => 'Name already existed'], 400);
            }
            $author = Author::query()->findOrFail($id);
            $avatar = $request->file('avatar');
            if($avatar && $avatar->isValid()) {
                $avatar_path = $avatar?->storeAs('/avatar', $avatar->hashName(), 'public');
                $author->avatar = $avatar_path;
            }
            $author->update($data);
            return response()->json($author);
        } catch (\Exception $exception) {
            DB::rollBack();
            if ($avatar_path != null) {
                Storage::disk('public')->delete($avatar_path);
            }
            return response()->json(['message' => $exception->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $author = Author::findOrFail($id);
        return response()->json($author);
    }

    public function destroy($id)
    {
        if (!auth()->user()->isAdmin())
            return response()->json(['message' => 'Unauthorized'], 401);
        $author = Author::query()->findOrFail($id);
        $count_product = $author->products()->count();
        if ($count_product > 0)
            return response()->json(['message' => 'Author cannot be deleted'], 500);
        $author->delete();
        return response()->json(['message' => 'Author deleted']);
    }
}
