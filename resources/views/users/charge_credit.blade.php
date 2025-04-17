@extends('layouts.master')
@section('title', 'Charge Customer Credit')
@section('content')
@if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Employee'))
<div class="row mt-4">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Charge Credit for {{ $user->name }}</h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <p>Current Credit Balance: <span class="badge bg-success">{{ $user->credit }}</span></p>
                </div>

                <form action="{{ route('save_credit', $user->id) }}" method="post">
                    @csrf
                    
                    @foreach($errors->all() as $error)
                    <div class="alert alert-danger">
                        <strong>Error!</strong> {{ $error }}
                    </div>
                    @endforeach
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount to Add:</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text">Please enter a positive amount to add to the customer's credit.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Add Credit</button>
                        <a href="{{ route('profile', $user->id) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-danger">
    <strong>Error!</strong> You are not authorized to access this page.
</div>
@endif
@endsection 