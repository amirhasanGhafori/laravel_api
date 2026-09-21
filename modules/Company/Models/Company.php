<?php

namespace Modules\Company\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Company\database\factories\CompanyFactory;
use Override;

class Company extends Model
{
    use HasFactory;


    protected $guarded = [];



    public function users(){
        return $this->hasMany('users');
    }

   protected static function newFactory(): CompanyFactory
   {
       return CompanyFactory::new();
   }
}
