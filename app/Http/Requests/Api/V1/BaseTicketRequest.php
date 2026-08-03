<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class BaseTicketRequest extends FormRequest
{



    public function mappedAttributes()
    {
        $attributeMap = [
            'data.attributes.title' => 'title',
            'data.attributes.description' => 'description',
            'data.attributes.status' => 'status',
            'data.attributes.created_at' => 'created_at',
            'data.attributes.updated_at' => 'updated_at',
            'data.relationships.author.data.id' => 'user_id'
        ];


        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }


        return $attributesToUpdate;
    }


    #[Override]
    public function messages()
    {
        return [
            'data.attributes.status' => 'the data.attributes.status value is invalid. please use A, C , H OR X.'
        ];
    }
}
