<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthorModel;

class AuthorModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AuthorModel::factory(20)->create();
    }
}
