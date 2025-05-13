@extends('layouts.master')
@section('title', 'Cryptography')
@section('content')
<div class="card m-4">
    <div class="card-body">
        <form action="{{ route('cryptography') }}" method="get">
            @csrf
            <div class="row mb-2">
                <div class="col">
                    <label for="data" class="form-label">Data:</label>
                    <textarea class="form-control" placeholder="Data" name="data" required>{{ $data }}</textarea>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <label for="action" class="form-label">Operation:</label>
                    <select class="form-control" name="action" required>
                        <option value="Encrypt" {{ $action == "Encrypt" ? "selected" : "" }}>Encrypt</option>
                        <option value="Decrypt" {{ $action == "Decrypt" ? "selected" : "" }}>Decrypt</option>
                        <option value="Hash" {{ $action == "Hash" ? "selected" : "" }}>Hash</option>
                        <option value="Sign" {{ $action == "Sign" ? "selected" : "" }}>Sign</option>
                        <option value="Verify" {{ $action == "Verify" ? "selected" : "" }}>Verify</option>
                        <option value="KeySend" {{ $action == "KeySend" ? "selected" : "" }}>KeySend</option>
                        <option value="KeyRecive" {{ $action == "KeyRecive" ? "selected" : "" }}>KeyRecive</option>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <label for="result" class="form-label">Result:</label>
                    <textarea class="form-control" placeholder="Data" name="result">{{ $result }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
<div class="card m-4">
    <div class="card-body">
        <strong>Result Status:</strong> {{ $status }}
    </div>
</div>
@endsection