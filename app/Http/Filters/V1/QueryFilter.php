<?php

namespace App\Http\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{

    protected Builder $builder;
    protected Request $request;
    protected $sortable = [];


    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $builder;
    }

    protected function filter($arr)
    {
        foreach ($arr as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }


    protected function sort($value){
        $sortAttrubutes = explode(',',$value);
        foreach($sortAttrubutes as $sortAttrubute){
            $direction = 'asc';
            if(strpos($sortAttrubute,'-')===0){
                $direction = 'desc';
                $sortAttrubute = substr($sortAttrubute,1);
            }

            if(!in_array($sortAttrubute,$this->sortable) && !array_key_exists($sortAttrubute,$this->sortable)){
                continue;
            }

            $columnName = $this->sortable[$sortAttrubute] ?? null;

            if($columnName === null){
                $columnName = $sortAttrubute;
            }

            $this->builder->orderBy($columnName,$direction);
        }
    }
}
