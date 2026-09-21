<?php

namespace Modules\Post\Database\Factories;

use Modules\Post\Models\Post;
use Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{

    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);
        return [
            'author_id'    => User::factory(),
            'title'        => $title,
            'slug'         => Str::slug($title),
            'body'         => fake()->paragraphs(5, true),
            'published_at' => fake()->optional(0.7)->dateTimeBetween('-1 year', 'now'),
        ];
    }

}
