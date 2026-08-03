<?php

namespace App\Models;

use App\Http\Filters\V1\QueryFilter;
use App\Policies\V1\TicketPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(TicketPolicy::class)]
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'id'
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeFilter(Builder $builder , QueryFilter $filters){
        return $filters->apply($builder);
    }
}
