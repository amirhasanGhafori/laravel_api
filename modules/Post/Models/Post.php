<?php

namespace Modules\Post\Models;

use App\Jobs\ReconcileAccount;
use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Post\Database\Factories\PostFactory;
use Modules\User\Models\User;

class Post extends Model
{
    use HasFactory;


    protected $guarded = [];


    protected $casts = [
        'published_at' => 'datetime',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function handle($string, Closure $next)
    {
        // Process the string
        $user = User::find(11);
        ReconcileAccount::dispatch($user)->onQueue('redis');
        // Pass the processed string to the next closure
        return $next($string);
    }


    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }


    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
