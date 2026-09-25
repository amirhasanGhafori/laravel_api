<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Models\Ticket;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends ApiController
{

    protected $policyClass = TicketPolicy::class;
    /**
     * Display a listing of the resource.
     */
    public function index(TicketFilter $filters)
    {
        // if ($this->include('author')) {
        //     return TicketResource::collection(Ticket::with('user')->paginate());
        // }
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        try {
            $this->isAble('create', Ticket::class);
        } catch (ModelNotFoundException $th) {
            $this->ok('User Not Found', [
                'error' => 'the provided user id does not exists.'
            ]);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to store that resource.', Response::HTTP_FORBIDDEN);
        }

        return new TicketResource(Ticket::create($request->mappedAttributes()));
    }

    /**
     * Display the specified resource.
     */
    public function show($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            if ($this->include('author')) {
                return new TicketResource($ticket->load('user'));
            }

            return new TicketResource($ticket);
        } catch (\Throwable $th) {
            return $this->error('Ticket Not Found.', Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrfail($ticket_id);

            $this->isAble('update', $ticket);

            $ticket->update($request->mappedAttributes());


            return new TicketResource($ticket);
        } catch (ModelNotFoundException $th) {
            return $this->error($th->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to update that resource.', Response::HTTP_FORBIDDEN);
        }
    }


    public function replace(ReplaceTicketRequest $request, $ticket_id)
    {

        try {
            $ticket = Ticket::findOrfail($ticket_id);

            $model = [
                'title' => $request->input('data.attributes.title'),
                'description' => $request->input('data.attributes.description'),
                'status' => $request->input('data.attributes.status'),
                'user_id' => $request->input('data.relationships.author.data.id')
            ];

            $this->isAble('replace', $ticket);



            $ticket->update($model);


            return new TicketResource($ticket);
        } catch (ModelNotFoundException $th) {
            return $this->error('Ticket Not Found', Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to Replace that resource.', Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            $this->isAble('delete', $ticket);

            $ticket->delete();
            return $this->ok('Ticket Successfully Delete');
        } catch (ModelNotFoundException $th) {
            return $this->error('Ticket not found.', Response::HTTP_NOT_FOUND);
        } catch (AuthorizationException $ex) {
            return $this->error('You are not authorized to Delete that resource.', Response::HTTP_FORBIDDEN);
        }
    }
}
