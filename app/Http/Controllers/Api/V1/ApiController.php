<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\traits\ApiResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use ApiResponse;
    
    public function include(string $relationship): bool
    {
        $param = request()->input('include');
        if (!isset($param)) {
            return false;
        }

        $includeValues = explode(',', strtolower($param));
        return in_array(strtolower($relationship), $includeValues);
    }
}
