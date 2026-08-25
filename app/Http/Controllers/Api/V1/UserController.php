<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\AuthorFilter;
use App\Http\Requests\Api\V1\ReplaceUserRequest;
use App\Models\User;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(AuthorFilter $filter)
    {
        return UserResource::collection(User::select('users.*')->join('tickets', 'user_id', '=', 'tickets.user_id')->filter($filter)->distinct()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $this->isAble('create', User::class);
            return new UserResource(User::create($request->mappedAttributes()));
        } catch (ModelNotFoundException $th) {
            $this->ok('User Not Found', [
                'error' => 'the provided user id does not exists.'
            ]);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to store that resource.', 403);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            return new UserResource($user);
        } catch (ModelNotFoundException $th) {
            return $this->ok('User Not Found', [
                'error' => 'the provided user id does not exists.'
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $user_id)
    {
        try {
            $user = User::findOrfail($user_id);

            $this->isAble('update', $user);

            $user->update($request->mappedAttributes());


            return new UserResource($user);
        } catch (ModelNotFoundException $th) {
            return $this->error($th->getMessage(), 404);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to update that resource.', 403);
        }
    }


    public function replace(ReplaceUserRequest $request, $user_id)
    {

        try {
            $user = User::findOrfail($user_id);


            $this->isAble('replace', $user);



            $user->update($request->mappedAttributes());


            return new UserResource($user);
        } catch (ModelNotFoundException $th) {
            return $this->error('Ticket Not Found', 404);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to Replace that resource.', 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            $this->isAble('delete', $user);

            $user->delete();
            return $this->ok('User Successfully Delete');
        } catch (ModelNotFoundException $th) {
            return $this->error('Ticket not found.', 404);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to Delete that resource.', 403);
        }
    }
}
