<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AuthorTicketsController extends ApiController
{
    protected $policyClass = TicketPolicy::class;
    public function index(string $author_id, TicketFilter $filters)
    {
        return TicketResource::collection(Ticket::where('user_id', $author_id)->filter($filters)->paginate());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request, $author_id)
    {
        try {
            $this->isAble('create', Ticket::class);
            return new TicketResource(Ticket::create($request->mappedAttributes()));
        } catch (ModelNotFoundException $th) {
            $this->ok('User Not Found', [
                'error' => 'the provided user id does not exists.'
            ]);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to store that resource.', 403);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, $author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::where('id', $ticket_id)
                ->where('user_id', $author_id)
                ->firstOrFail();

            $this->isAble('update', $ticket);

            $ticket->update($request->mappedAttributes());


            return new TicketResource($ticket);
        } catch (ModelNotFoundException $th) {
            return $this->error('Ticket Not Found', 404);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to update that resource.', 403);
        }
    }

    public function replace(ReplaceTicketRequest $request, $author_id, $ticket_id)
    {

        try {
            $ticket = Ticket::where('id', $ticket_id)
                ->where('user_id', $author_id)
                ->firstOrFail();


            $this->isAble('replace', $ticket);

            $ticket->update($request->mappedAttributes());
            return new TicketResource($ticket);
        } catch (ModelNotFoundException $th) {
            return $this->error('Ticket Not Found', 404);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to replace that resource.', 403);
        }
    }


    public function destroy($user_id, $ticket_id)
    {
        try {
            $ticket = Ticket::where('id', $ticket_id)
                ->where('user_id', $user_id)
                ->firstOrFail();

            $this->isAble('delete', $ticket);
            $ticket->delete();

            return $this->error('Ticket Not Deleted.You Not Permission', 403);
        } catch (ModelNotFoundException $th) {
            return $this->error('Ticket Not Found', 404);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to Delete that resource.', 403);
        }
    }
}
