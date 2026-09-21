<?php

namespace App\Http\Controllers;

use App\Jobs\ReconcileAccount;
use Modules\Post\Models\Post as ModelsPost;
use Modules\User\Models\User;

class PostController extends Controller
{
    public function index()
    {
        $user = User::find(11);
        dispatch(new ReconcileAccount($user))->onQueue('redis');


        $posts = ModelsPost::with('author')
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
