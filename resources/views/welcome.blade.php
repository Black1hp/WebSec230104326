@extends('layouts.app')
@section('title', 'Welcome')
@section('content')

    <div class="card m-4">
        <div class="card-body">
            <h3>Welcome to WebSecService</h3>
            @auth
                @if(auth()->user()->role === 'customer' || auth()->user()->role === 'user')
                    <div class="alert alert-info mt-3">
                        <strong>Your Credit Balance:</strong> ${{ number_format(auth()->user()->credit, 2) }}
                        <a href="{{ route('profile') }}" class="btn btn-sm btn-primary ms-2">View Profile</a>
                    </div>
                @endif
            @endauth
        </div>
    </div>
    <script>
        function doSomething() {
            alert("Hello from Java Script");
        }
    </script>

    <div class="card m-4">
        <div class="card-header">Basic Web Page with Bootstrap</div>
        <div class="card-body">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
        </div>
    </div>

    <div class="card m-4">
        <div class="card-body">
            <button type="button" class="btn btn-primary" onclick="doSomething()">Press Me</button>
        </div>
    </div>
    <div class="card m-4">
        <div class="card-body">
            <button type="button" id="btn1" class="btn btn-primary" >Press Me</button>
            <button type="button" id="btn2" class="btn btn-success" style="display: none;" >Press Me Again</button>
            <ul id="ul1">
            </ul>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            console.log('Document ready');

            $("#btn1").click(function(){
                console.log('Button 1 clicked');
                $("#btn2").show();
            });

            $("#btn2").click(function(){
                console.log('Button 2 clicked');
                $("#ul1").append("<li>Hello</li>");
            });
        });
    </script>
@endsection
