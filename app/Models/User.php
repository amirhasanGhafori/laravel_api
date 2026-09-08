<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Filters\V1\QueryFilter;
use App\Policies\V1\UserPolicy;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PhpParser\Node\Expr\FuncCall;
use App\Model as ModelCustom;

#[Fillable(['name', 'email', 'password', 'is_manager'])]
#[Hidden(['password', 'remember_token'])]
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_manager' => 'boolean',
            'last_login_at' => 'datetime'
        ];
    }



    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }



    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function logins()
    {
        return $this->hasMany(Login::class);
    }


    public function lastLogin()
    {
        return $this->belongsTo(Login::class);
    }


    public function scopeWithLastLogin($query)
    {
        $query->addSelect([
            'last_login_id' => Login::select('id')
                ->whereColumn('user_id', 'users.id')
                ->latest()
                ->take(1)

        ])->with('lastLogin');
    }


    public function lastCompany()
    {
        return $this->belongsTo(Company::class);
    }


    public function scopeWithCompanyName($query)
    {
        $query->addSelect([
            'last_company_id' => Company::select('id')
                ->whereColumn('id', 'users.company_id')
        ])->with('lastCompany');
    }






    public function scopeSearch($query, $searchTerm = null)
    {
        //speed 8 ms



        collect(str_getcsv($searchTerm, ' ', '"'))
            ->filter()
            ->each(function ($term) use ($query) {
                $query->where(function ($query) use ($term) {
                    $query->where('firstname', 'like', "{$term}%")
                        ->orWhere('lastname', 'like', "{$term}%")
                        ->orWhereIn('company_id', Company::query()
                            ->where('name', 'like', "{$term}%")->pluck('id'));
                });
            });



        //speed 140ms
        //step 1
        // collect(str_getcsv($searchTerm, ' ', '"'))
        //     ->filter()
        //     ->each(function ($term) use ($query) {
        //         $query->where(function ($query) use ($term) {
        //             $query->where('firstname', 'like', "{$term}%")
        //                 ->orWhere('lastname', 'like', "{$term}%")
        //                 ->orWhereIn('company_id', Company::select('id')
        //                     ->where('name', 'like', "{$term}%"));
        //         });
        //     });



        //speed 680ms
        //Step 2 <================================>
        // $query->join('companies', 'companies.id', '=', 'users.company_id');
        // collect(str_getcsv($searchTerm, ' ', '"'))
        //     ->filter()
        //     ->each(function ($term) use ($query) {
        //         $query->where(function ($query) use ($term) {
        //             $query->where('firstname', 'like', "{$term}%")
        //                 ->orWhere('lastname', 'like', "{$term}%")
        //                 ->orWhere('companies.name', 'like', "{$term}%");
        //         });
        //     });



        //speed 1s
        //Step 1 <=============================>
        // collect(str_getcsv($searchTerm, ' ', '"'))
        // ->filter()
        // ->each(function ($term) use ($query) {
        //     $query->where(function ($query) use ($term) {
        //         $query->where('firstname', 'like', "{$term}%")
        //             ->orWhere('lastname', 'like', "{$term}%")
        //             ->orWhereHas('company', function ($query) use ($term) {
        //                 $query->where('name', 'like', "{$term}%");
        //             });
        //     });
        // });
    }
}
