<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'price' => 'Free',
                'period' => '(30 Days Trial)',
                'is_popular' => false,
                'features' => [
                    '1 Admin, 5 Teachers',
                    'Image uploads only',
                    'Custom logo',
                    'Light & Dark mode',
                    'Pin announcements',
                    'Timeline view',
                ]
            ],
            [
                'name' => 'Pro',
                'price' => '₱2,499',
                'period' => '/mo',
                'is_popular' => true,
                'features' => [
                    '5 Admins, 15 Teachers',
                    'Image & Video uploads',
                    'Categories',
                    'Theme customization',
                    'Custom logo',
                    'Light & Dark mode',
                    'Pin announcements',
                    'Timeline view',
                    'Reports',
                ]
            ],
            [
                'name' => 'Ultimate',
                'price' => '₱4,999',
                'period' => '/mo',
                'is_popular' => false,
                'features' => [
                    '10 Admins, ∞ Teachers',
                    'Image & Video uploads',
                    'Categories',
                    'Theme customization',
                    'Pre-built templates',
                    'Custom logo',
                    'Light & Dark mode',
                    'Pin announcements',
                    'Timeline view',
                    'Reports',
                ]
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\Plan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
