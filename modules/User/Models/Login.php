<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\database\factories\LoginFactory;

class Login extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected static function newFactory(): LoginFactory
    {
        return LoginFactory::new();
    }
}
