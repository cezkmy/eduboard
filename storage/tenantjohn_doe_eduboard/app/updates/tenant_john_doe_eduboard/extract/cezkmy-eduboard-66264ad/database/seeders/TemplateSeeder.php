<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\Category;
use App\Models\TemplateType;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Artistic',
            'Fairy',
            'Academic',
            'National',
            'Nature',
            'Weather',
            'Seasonal'
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category]);
        }

        $types = [
            'Basic',
            'Pro',
            'Ultimate'
        ];

        foreach ($types as $type) {
            TemplateType::firstOrCreate(['name' => $type]);
        }

        $templates = [
            ['name' => 'Classic Parchment', 'category' => 'Artistic', 'type' => 'Basic', 'image' => 'bt1.jpg'],
            ['name' => 'Floral Ribbon', 'category' => 'Fairy', 'type' => 'Pro', 'image' => 'bt2.jpg'],
            ['name' => 'Academic Gold', 'category' => 'Academic', 'type' => 'Ultimate', 'image' => 'bt3.jpg'],
            ['name' => 'National Pride', 'category' => 'National', 'type' => 'Ultimate', 'image' => 'bt3_national.png'],
            ['name' => 'Nature Grove', 'category' => 'Nature', 'type' => 'Basic', 'image' => 'bt4.jpg'],
            ['name' => 'Sky Weather', 'category' => 'Weather', 'type' => 'Pro', 'image' => 'bt5.jpg'],
            ['name' => 'Spring Blossom', 'category' => 'Seasonal', 'type' => 'Basic', 'image' => 'bt6.jpg'],
            ['name' => 'Winter Frost', 'category' => 'Seasonal', 'type' => 'Ultimate', 'image' => 'bt7.jpg'],
        ];

        foreach ($templates as $template) {
            Template::firstOrCreate($template);
        }
    }
}
