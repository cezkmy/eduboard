<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('page_admin_categories')) {
            abort(403, 'Unauthorized. Only administrators can manage categories.');
        }

        if (!tenant()->hasFeature('categories')) abort(403, 'Upgrade your plan to access Categories.');

        $schoolType = tenant('school_type') ?? 'college';

        $categories = Category::where('type', 'announcement_category')->get();
        $colleges = Category::where('type', 'college')->get();
        $yearLevels = Category::where('type', 'level')->get();
        $gradeLevels = Category::where('type', 'grade_level')->get();
        $programs = Category::where('type', 'program')->get();
        $strands = Category::where('type', 'strand')->get();
        $sections = Category::where('type', 'section')->get();

        return view('tenant_ui.admin.categories', compact('categories', 'colleges', 'yearLevels', 'gradeLevels', 'programs', 'strands', 'sections', 'schoolType'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('page_admin_categories')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. You do not have permission to create categories.'], 403);
            }
            abort(403, 'Unauthorized.');
        }

        if (!tenant()->hasFeature('categories')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Categories feature is not available on your current plan.'], 403);
            }
            abort(403, 'Upgrade your plan to access Categories.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'color' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'educational_level' => 'nullable|in:elementary,junior_high,senior_high,college'
        ]);

        Category::create($request->all());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => ucfirst(str_replace('_', ' ', $request->type)) . ' added successfully.']);
        }

        return back()->with('success', ucfirst(str_replace('_', ' ', $request->type)) . ' added successfully.');
    }

    public function updateType(Request $request)
    {
        if (!auth()->user()->hasPermission('page_admin_settings')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate(['school_type' => 'required|in:college,highschool']);
        
        tenant()->update(['school_type' => $request->school_type]);

        return back()->with('success', 'School structure updated to ' . ($request->school_type === 'college' ? 'Higher Education' : 'K-12 / Senior High') . '.');
    }

    public function generatePresets(Request $request)
    {
        if (!auth()->user()->hasPermission('page_admin_categories')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate(['type' => 'required|in:elementary,jhs,shs,college']);
        $type = $request->type;
        $created = 0;

        if ($type === 'elementary') {
            for ($i = 1; $i <= 6; $i++) {
                Category::firstOrCreate(['name' => "Grade $i", 'type' => 'grade_level', 'educational_level' => 'elementary']); $created++;
            }
        } elseif ($type === 'jhs') {
            for ($i = 7; $i <= 10; $i++) {
                Category::firstOrCreate(['name' => "Grade $i", 'type' => 'grade_level', 'educational_level' => 'junior_high']); $created++;
            }
        } elseif ($type === 'shs') {
            Category::firstOrCreate(['name' => "Grade 11", 'type' => 'grade_level', 'educational_level' => 'senior_high']);
            Category::firstOrCreate(['name' => "Grade 12", 'type' => 'grade_level', 'educational_level' => 'senior_high']);
            $created += 2;
        } elseif ($type === 'college') {
            $levels = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];
            foreach ($levels as $level) {
                Category::firstOrCreate(['name' => $level, 'type' => 'level', 'educational_level' => 'college']); $created++;
            }
        }

        return back()->with('success', "Successfully generated presets for " . strtoupper($type));
    }

    public function destroy(Category $category)
    {
        if (!auth()->user()->hasPermission('page_admin_categories')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. You do not have permission to delete categories.'], 403);
            }
            abort(403, 'Unauthorized.');
        }

        if (!tenant()->hasFeature('categories')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Categories feature is not available on your current plan.'], 403);
            }
            abort(403, 'Upgrade your plan to access Categories.');
        }

        $category->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Item deleted successfully.']);
        }

        return back()->with('success', 'Item deleted successfully.');
    }
}
