<?php

namespace Database\Factories;

use App\Models\Packet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Packet>
 */
class PacketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tracking_code'       => strtoupper($this->faker->bothify('???-####')),
            'recipient_name'      => $this->faker->name(),
            'recipient_email'     => $this->faker->safeEmail(),
            'destination_address' => $this->faker->address(),
            'weight_grams'        => $this->faker->numberBetween(100, 10000),
        ];
    }
}
