<?php

use App\Providers\AppServiceProvider;
use Modules\Post\Providers\RouteServiceProvider;

return [
    AppServiceProvider::class,
    Modules\Post\Providers\PostServiceProvider::class,
    Modules\Ticket\Providers\TicketServiceProvider::class,
    Modules\Company\Providers\CompanyServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
];
