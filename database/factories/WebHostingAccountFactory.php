<?php

namespace Database\Factories;

use App\Models\WebHostingAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebHostingAccount>
 */
class WebHostingAccountFactory extends Factory
{
    protected $model = WebHostingAccount::class;

    public function definition(): array
    {
        return [
            'domain' => $this->faker->domainName(),
            'username' => $this->faker->userName(),
            'password' => $this->faker->password(),
            'control_panel' => 'cpanel',
            'status' => 'active',
        ];
    }
}
