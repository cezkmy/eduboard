<?php

namespace Database\Seeders\Tenant;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories to avoid duplicates with wrong types
        Category::truncate();

        // Announcement Categories
        $announcementCategories = [
            ['name' => 'General', 'type' => 'announcement_category', 'color' => '#6b7280'],
            ['name' => 'Academic', 'type' => 'announcement_category', 'color' => '#3b82f6'],
            ['name' => 'Events', 'type' => 'announcement_category', 'color' => '#10b981'],
            ['name' => 'Administrative', 'type' => 'announcement_category', 'color' => '#8b5cf6'],
            ['name' => 'Emergency', 'type' => 'announcement_category', 'color' => '#ef4444'],
        ];

        foreach ($announcementCategories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name'], 'type' => $cat['type']],
                ['color' => $cat['color']]
            );
        }

        // Higher Education Structures (Colleges)
        $colleges = [
            ['name' => 'COT', 'type' => 'college'],
            ['name' => 'COB', 'type' => 'college'],
            ['name' => 'CON', 'type' => 'college'],
            ['name' => 'COE', 'type' => 'college'],
            ['name' => 'CAS', 'type' => 'college'],
        ];

        foreach ($colleges as $college) {
            Category::updateOrCreate(['name' => $college['name'], 'type' => $college['type']]);
        }

        // Year Levels (College)
        $yearLevels = [
            ['name' => '1st Year', 'type' => 'level'],
            ['name' => '2nd Year', 'type' => 'level'],
            ['name' => '3rd Year', 'type' => 'level'],
            ['name' => '4th Year', 'type' => 'level'],
            ['name' => '5th Year', 'type' => 'level'],
        ];

        foreach ($yearLevels as $level) {
            Category::updateOrCreate(['name' => $level['name'], 'type' => $level['type']]);
        }

        // Grade Levels (High School)
        $gradeLevels = [
            ['name' => 'Grade 11', 'type' => 'grade_level'],
            ['name' => 'Grade 12', 'type' => 'grade_level'],
        ];

        foreach ($gradeLevels as $level) {
            Category::updateOrCreate(['name' => $level['name'], 'type' => $level['type']]);
        }

        // Strands (High School)
        $strands = [
            ['name' => 'STEM', 'type' => 'strand'],
            ['name' => 'ABM', 'type' => 'strand'],
            ['name' => 'HUMSS', 'type' => 'strand'],
            ['name' => 'GAS', 'type' => 'strand'],
            ['name' => 'TVL', 'type' => 'strand'],
        ];

        foreach ($strands as $strand) {
            Category::updateOrCreate(['name' => $strand['name'], 'type' => $strand['type']]);
        }

        // Programs (College)
        $programs = [
            ['name' => 'BSIT', 'type' => 'program'],
            ['name' => 'BSCS', 'type' => 'program'],
            ['name' => 'BSBA', 'type' => 'program'],
            ['name' => 'BSN', 'type' => 'program'],
            ['name' => 'BSED', 'type' => 'program'],
        ];

        foreach ($programs as $program) {
            Category::updateOrCreate(['name' => $program['name'], 'type' => $program['type']]);
        }

        // Sections
        $sections = [
            ['name' => 'Section A', 'type' => 'section'],
            ['name' => 'Section B', 'type' => 'section'],
            ['name' => 'Section C', 'type' => 'section'],
        ];

        foreach ($sections as $section) {
            Category::updateOrCreate(['name' => $section['name'], 'type' => $section['type']]);
        }
    }
}
