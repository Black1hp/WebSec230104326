<?php

namespace App\Http\Controllers\Web;

use App\Models\Grade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GradesController extends Controller
{
    /**
     * Display a listing of the grades.
     */
    public function index(Request $request)
    {
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
        return view('grades.edit', compact('grade'));
    }

    /**
     * Store or update the specified grade in storage.
     */
    public function save(Request $request, Grade $grade = null)
    {
        // If no $grade is provided, create a new instance.
        $grade = $grade ?? new Grade();

        // Fill model with request data (ensure Grade::$fillable matches these fields).
        $grade->fill($request->all());
        $grade->save();

        // Redirect to the grades index page (adjust route name if needed).
        return redirect()->route('grades.index');
    }

    /**
     * Remove the specified grade from storage.
     */
    public function delete(Request $request, Grade $grade)
    {
        $grade->delete();
        return redirect()->route('grades.index');
    }
}
