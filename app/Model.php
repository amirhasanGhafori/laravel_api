<?php


namespace App;

use Exception;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Override;

class Model extends Eloquent
{
    #[Override]
    public function getRelationshipFromMethod($method)
    {
       $class = get_class($this);
        throw new Exception("Lazy loading of relationships is not allowed on {$class}.");
    }
} 