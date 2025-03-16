@extends('layouts.app')

@section('title', 'All Grades')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>All Grades</h1>
        <a href="{{ route('grades.edit') }}" class="btn btn-primary">Create New Grade</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Subject</th>
            <th>Mark</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($grades as $grade)
            <tr>
                <td>{{ $grade->id }}</td>
                <td>{{ $grade->user_id }}</td>
                <td>{{ $grade->subject }}</td>
                <td>{{ $grade->mark }}</td>
                <td>
                    <a href="{{ route('grades.edit', $grade->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('grades.delete', $grade->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                            Delete
                        </button>
                    </form>

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
