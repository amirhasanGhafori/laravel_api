<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author')
            ->when(request('search'), function ($query, $search) {
                $query
                    ->selectRaw('*, MATCH(title, body) AGAINST(? IN BOOLEAN MODE) AS score', [$search])
                    ->whereRaw('MATCH(title, body) AGAINST(? IN BOOLEAN MODE)', [$search]);
            },function ($query) {
                $query->latest('published_at');
            })
            ->paginate(20);


        return view('posts', compact('posts'));
    }
}
