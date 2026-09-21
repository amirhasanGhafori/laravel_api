<?php

return [
    'model' => [
        'class' => 'Modules\User\Entities\User',
        'table' => 'users',
    ],
    'resource' => [
        'class' => 'Modules\User\Http\Resources\UserResource',
    ],
];
