@extends('layouts.app')

@section('title', $grade->id ? 'Edit Grade' : 'Create Grade')

@section('content')
    <h1>{{ $grade->id ? 'Edit Grade' : 'Create Grade' }}</h1>

    <form action="{{ route('grades.save', $grade->id) }}" method="POST" class="mt-4">
        @csrf
        <div class="mb-3">
            <label for="user_id" class="form-label">User ID:</label>
            <input type="text" name="user_id" id="user_id" class="form-control"
                   value="{{ old('user_id', $grade->user_id) }}">
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Subject:</label>
            <input type="text" name="subject" id="subject" class="form-control"
                   value="{{ old('subject', $grade->subject) }}">
        </div>

        <div class="mb-3">
            <label for="mark" class="form-label">Mark:</label>
            <input type="number" name="mark" id="mark" class="form-control"
                   value="{{ old('mark', $grade->mark) }}">
        </div>

        <button type="submit" class="btn btn-success">
            {{ $grade->id ? 'Update' : 'Save' }}
        </button>
        <a href="{{ route('grades.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
