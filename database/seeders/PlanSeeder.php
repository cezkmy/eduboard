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
                'period' => '',
                'is_popular' => false,
                'features' => [
                    '1 Admin, 5 Teachers',
                    'Teacher upload enabled',
                    'Image uploads only (JPEG, PNG)',
                    'Light & Dark mode',
                    'Pin announcements',
                    'Timeline view'
                ]
            ],
            [
                'name' => 'Pro',
                'price' => '₱199',
                'period' => '/mo',
                'is_popular' => true,
                'features' => [
                    '5 Admins, 15 Teachers',
                    'Teacher upload enabled',
                    'Image & Video uploads (MP4 max 100MB)',
                    'Custom logo',
                    'Light & Dark mode',
                    'Pin announcements',
                    'Timeline view',
                    'Announcement categories',
                    'Background theme customization'
                ]
            ],
            [
                'name' => 'Ultimate',
                'price' => '₱299',
                'period' => '/mo',
                'is_popular' => false,
                'features' => [
                    '10 Admins, Unlimited Teachers',
                    'Teacher upload enabled',
                    'Image & Video uploads (MP4 max 100MB)',
                    'Custom logo',
                    'Light & Dark mode',
                    'Pin announcements',
                    'Timeline view',
                    'Announcement categories',
                    'Background theme customization',
                    'Pre-built announcement templates'
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
