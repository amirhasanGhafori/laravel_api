<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class BaseUserRequest extends FormRequest
{



    public function mappedAttributes(array $atherAttributes = [])
    {
        $attributeMap = array_merge(
            [
                'data.attributes.name' => 'name',
                'data.attributes.email' => 'email',
                'data.attributes.isManager' => 'is_manager',
                'data.attributes.password' => 'password',
            ]
        ,$atherAttributes);


        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            if ($this->has($key)) {
                $value = $this->input($key);

                if($attribute === 'password'){
                    $value = bcrypt($value);
                }
                $attributesToUpdate[$attribute] = $value;
            }
        }


        return $attributesToUpdate;
    }

}
