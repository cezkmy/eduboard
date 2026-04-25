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

        // College Structures
        $colleges = [
            ['name' => 'COT', 'type' => 'college', 'educational_level' => 'college'],
            ['name' => 'COB', 'type' => 'college', 'educational_level' => 'college'],
            ['name' => 'CON', 'type' => 'college', 'educational_level' => 'college'],
            ['name' => 'COE', 'type' => 'college', 'educational_level' => 'college'],
            ['name' => 'CAS', 'type' => 'college', 'educational_level' => 'college'],
            // K-12 Departments (reusing the 'college' type which represents the top-level organization/department)
            ['name' => 'Elementary Department', 'type' => 'college', 'educational_level' => 'elementary'],
            ['name' => 'Junior High Department', 'type' => 'college', 'educational_level' => 'junior_high'],
            ['name' => 'Senior High Department', 'type' => 'college', 'educational_level' => 'senior_high'],
        ];

        foreach ($colleges as $college) {
            Category::updateOrCreate(
                ['name' => $college['name'], 'type' => $college['type']],
                ['educational_level' => $college['educational_level']]
            );
        }

        // College Programs
        $programs = [
            ['name' => 'BSIT', 'type' => 'program', 'educational_level' => 'college'],
            ['name' => 'BSCS', 'type' => 'program', 'educational_level' => 'college'],
            ['name' => 'BSBA', 'type' => 'program', 'educational_level' => 'college'],
            ['name' => 'BSN', 'type' => 'program', 'educational_level' => 'college'],
            ['name' => 'BSED', 'type' => 'program', 'educational_level' => 'college'],
        ];

        foreach ($programs as $program) {
            Category::updateOrCreate(
                ['name' => $program['name'], 'type' => $program['type']],
                ['educational_level' => $program['educational_level']]
            );
        }

        // College Year Levels
        $yearLevels = [
            ['name' => '1st Year', 'type' => 'level', 'educational_level' => 'college'],
            ['name' => '2nd Year', 'type' => 'level', 'educational_level' => 'college'],
            ['name' => '3rd Year', 'type' => 'level', 'educational_level' => 'college'],
            ['name' => '4th Year', 'type' => 'level', 'educational_level' => 'college'],
            ['name' => '5th Year', 'type' => 'level', 'educational_level' => 'college'],
        ];

        foreach ($yearLevels as $level) {
            Category::updateOrCreate(
                ['name' => $level['name'], 'type' => $level['type']],
                ['educational_level' => $level['educational_level']]
            );
        }

        // College Sections
        $sections = [
            ['name' => 'Section A', 'type' => 'section', 'educational_level' => 'college'],
            ['name' => 'Section B', 'type' => 'section', 'educational_level' => 'college'],
            ['name' => 'Section C', 'type' => 'section', 'educational_level' => 'college'],
        ];

        foreach ($sections as $section) {
            Category::updateOrCreate(
                ['name' => $section['name'], 'type' => $section['type']],
                ['educational_level' => $section['educational_level']]
            );
        }

        // Elementary (Grade 1-6)
        for ($i = 1; $i <= 6; $i++) {
            Category::updateOrCreate(
                ['name' => "Grade $i", 'type' => 'grade_level'],
                ['educational_level' => 'elementary']
            );
        }

        // Junior High (Grade 7-10)
        for ($i = 7; $i <= 10; $i++) {
            Category::updateOrCreate(
                ['name' => "Grade $i", 'type' => 'grade_level'],
                ['educational_level' => 'junior_high']
            );
        }

        // Senior High (Grade 11-12)
        for ($i = 11; $i <= 12; $i++) {
            Category::updateOrCreate(
                ['name' => "Grade $i", 'type' => 'grade_level'],
                ['educational_level' => 'senior_high']
            );
        }

        // Senior High Strands
        $strands = [
            ['name' => 'STEM', 'type' => 'strand', 'educational_level' => 'senior_high'],
            ['name' => 'ABM', 'type' => 'strand', 'educational_level' => 'senior_high'],
            ['name' => 'HUMSS', 'type' => 'strand', 'educational_level' => 'senior_high'],
            ['name' => 'GAS', 'type' => 'strand', 'educational_level' => 'senior_high'],
            ['name' => 'TVL', 'type' => 'strand', 'educational_level' => 'senior_high'],
        ];

        foreach ($strands as $strand) {
            Category::updateOrCreate(
                ['name' => $strand['name'], 'type' => $strand['type']],
                ['educational_level' => $strand['educational_level']]
            );
        }
    }
}
