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
        ->with(['logins', 'company'])
        ->search(request('search'))
        ->paginate(35);
        return view('users',['users'=>$users]);
    }
}
 