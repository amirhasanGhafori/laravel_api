<?php

namespace Tests\Unit;

use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MappedAttributesTest extends TestCase
{
    public function test_it_maps_ticket_json_api_fields_to_model_attributes(): void
    {
        $request = StoreTicketRequest::create('/', 'POST', [
            'data' => [
                'attributes' => [
                    'title' => 'Mapped title',
                    'description' => 'Mapped description',
                    'status' => 'A',
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'id' => 12,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'title' => 'Mapped title',
            'description' => 'Mapped description',
            'status' => 'A',
            'user_id' => 12,
        ], $request->mappedAttributes());
    }

    public function test_it_omits_ticket_fields_that_are_not_present(): void
    {
        $request = StoreTicketRequest::create('/', 'POST', [
            'data' => [
                'attributes' => [
                    'title' => 'Only title',
                ],
            ],
        ]);

        $this->assertSame([
            'title' => 'Only title',
        ], $request->mappedAttributes());
    }

    public function test_it_maps_user_json_api_fields_and_hashes_the_password(): void
    {
        $request = StoreUserRequest::create('/', 'POST', [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'isManager' => true,
                    'password' => 'secret-pass',
                ],
            ],
        ]);

        $mapped = $request->mappedAttributes();

        $this->assertSame('Jane Doe', $mapped['name']);
        $this->assertSame('jane@example.com', $mapped['email']);
        $this->assertTrue($mapped['is_manager']);
        $this->assertTrue(Hash::check('secret-pass', $mapped['password']));
    }
}
