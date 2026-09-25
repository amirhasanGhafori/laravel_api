<?php

namespace App\Http\Controllers;

use App\Jobs\ReconcileAccount;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Modules\Post\Models\Post as ModelsPost;
use Modules\User\Models\User;

class PostController extends Controller
{
    // public function index()
    // {
    //     $user = User::find(11);
    //     dispatch(new ReconcileAccount($user))->onQueue('redis');


    //     $posts = ModelsPost::with('author')
    //         ->when(request('search'), function ($query, $search) {
    //             $query
    //                 ->selectRaw('*, MATCH(title, body) AGAINST(? IN BOOLEAN MODE) AS score', [$search])
    //                 ->whereRaw('MATCH(title, body) AGAINST(? IN BOOLEAN MODE)', [$search]);
    //         },function ($query) {
    //             $query->latest('published_at');
    //         })
    //         ->paginate(20);


    //     return view('posts', compact('posts'));
    // }



    public function index()
    {
        $page = request('page', 1);

        $query = ModelsPost::select(['id', 'title', 'body', 'author_id', 'published_at', 'slug'])->with('author:id,firstName,lastName')
            ->when(request('search'), function ($query, $search) {
                $query
                    ->selectRaw('*, MATCH(title, body) AGAINST(? IN BOOLEAN MODE) AS score', [$search])
                    ->whereRaw('MATCH(title, body) AGAINST(? IN BOOLEAN MODE)', [$search]);
            }, function ($query) {
                $query->latest('published_at');
            });

        $total = $query->count();


        $posts = Cache::remember(
            "posts.index.page.$page",
            now()->addMinutes(15),
            function () use ($query, $page) {
                return $query->forPage($page, 20)->get()->toArray();
            }
        );

        $posts = new LengthAwarePaginator(
            $posts,
            $total,
            20,
            $page,
            [
                'path' => route('posts'),
                'query' => request()->query(),
            ]
        );


        return view('posts', compact('posts'));
    }
}
