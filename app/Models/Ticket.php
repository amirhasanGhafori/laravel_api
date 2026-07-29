<?php

namespace App\Models;

use App\Http\Filters\V1\QueryFilter;
use App\Http\Filters\V1\TicketFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeFilter(Builder $builder , QueryFilter $filters){
        return $filters->apply($builder);
    }
}
