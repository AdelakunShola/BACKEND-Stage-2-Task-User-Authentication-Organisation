<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Organization;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Organization::class;

    public function definition()
    {
        return [
            'orgId' => (string) Str::uuid(),
            'name' => $this->faker->company,
            'description' => $this->faker->catchPhrase,
        ];
    }
}
