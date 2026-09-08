<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index(){
        // $users = User::query()
        // ->select('id','name','company_id')
        // ->withLastLogin()
        // ->with('company')
        // ->orderBy('name')
        // ->simplePaginate();

        $users = User::query()
        ->with(['company'])
        ->withLastLogin()
        ->search(request('search'))
        ->orderBy('last_login_at', 'desc')
        ->paginate(35);
        
        return view('users',['users'=>$users]);
    }
}
 