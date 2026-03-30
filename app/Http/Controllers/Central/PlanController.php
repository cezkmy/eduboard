<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        // Must be central admin
        if (!auth()->user() || !auth()->user()->is_admin) {
            return redirect()->route('central.user.dashboard');
        }

        $plans = Plan::all();
        return view('central.admin.plans', compact('plans'));
    }

    public function update(Request $request, $id)
    {
        // Must be central admin
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }

        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'price' => 'required|string',
            'period' => 'nullable|string',
            'features' => 'required|string',
        ]);

        // Convert newline separated string to array
        $featuresArray = array_filter(array_map('trim', explode("\n", $validated['features'])));

        $plan->update([
            'price' => $validated['price'],
            'period' => $validated['period'],
            'features' => array_values($featuresArray),
        ]);

        return back()->with('success', 'Plan updated successfully!');
    }
}
