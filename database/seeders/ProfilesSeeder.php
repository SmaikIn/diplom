<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $faker = Faker::create();

        foreach ($users as $user) {
            $companies = $user->companies;
            foreach ($companies as $company) {
                Profile::create([
                    'uuid' => $faker->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'second_name' => $user->second_name,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'birthday' => $user->birthday,
                    'email' => $user->email,
                    'position' => $faker->jobTitle,
                    'description' => $faker->sentence,
                    'company_uuid' => $company->uuid,
                    'user_uuid' => $user->uuid,
                    'department_uuid' => $company->department()->inRandomOrder()->first()->uuid ?? null,
                    'role_uuid' => $company->roles()->inRandomOrder()->first()->uuid ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

}
