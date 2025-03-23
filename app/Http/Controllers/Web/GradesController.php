<?php

namespace App\Http\Controllers\Web;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class GradesController extends Controller
{
    /**
     * Display a listing of the grades.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Grade::class);
        $grades = Grade::all();
        return view('grades.index', compact('grades'));
    }

    /**
     * Show the form for creating/editing a grade.
     */
    public function edit(Request $request, Grade $grade = null)
    {
        // If no $grade is provided, create a new instance.
        $grade = $grade ?? new Grade();
        
        if ($grade->exists) {
            $this->authorize('edit', $grade);
        } else {
            $this->authorize('create', Grade::class);
        }
        
        return view('grades.edit', compact('grade'));
    }

    /**
     * Store or update the specified grade in storage.
     */
    public function save(Request $request, Grade $grade = null)
    {
        // If no $grade is provided, create a new instance.
        $isNew = !($grade && $grade->exists);
        $grade = $grade ?? new Grade();
        
        if ($isNew) {
            $this->authorize('create', Grade::class);
        } else {
            $this->authorize('edit', $grade);
        }

        // Validate the request data
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'mark' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // Fill model with validated data
        $grade->fill($validated);
        $grade->save();

        // Redirect to the grades index page
        return redirect()->route('grades.index');
    }

    /**
     * Show a specific grade.
     */
    public function show(Request $request, $gradeId)
    {
        try {
            $grade = Grade::findOrFail($gradeId);
            $this->authorize('view', $grade);
            return view('grades.show', compact('grade'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('grades.index')
                ->with('error', 'Grade not found.');
        } catch (\Exception $e) {
            Log::error('Error showing grade: ' . $e->getMessage());
            return redirect()->route('grades.index')
                ->with('error', 'An error occurred while trying to view this grade.');
        }
    }

    /**
     * Remove the specified grade from storage.
     */
    public function delete(Request $request, Grade $grade)
    {
        $this->authorize('delete', $grade);
        $grade->delete();
        return redirect()->route('grades.index');
    }
}
