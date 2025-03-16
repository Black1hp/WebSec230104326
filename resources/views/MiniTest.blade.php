@extends('layouts.app')
@section('title', 'Mini Test')
@section('content')



    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Mohamed Blog</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">All posts</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        {{--    @php--}}
        {{--        $list = [--}}
        {{--    ['name' => 'Juice', 'Quantity' => 47, 'Price' => 25],--}}
        {{--    ['name' => 'Water', 'Quantity' => 55, 'Price' => 10],--}}
        {{--    ['name' => 'Cola', 'Quantity' => 31, 'Price' => 15]--}}
        {{--    ];--}}
        {{--    @endphp--}}
        @php
            $student = [
                'name' => 'mohamed',
                'id' => '4326',
                'department' => 'cyber security',
                'GPA' => 4.1,
                'courses' => [
                    ['code' => 'ACD', 'name' => 'Web Security', 'grade' => 4],
                    ['code' => 'NCD', 'name' => 'Operating System', 'grade' => 4],
                    ['code' => 'AWF', 'name' => 'Digital forencics', 'grade' => 4],
                    ['code' => 'ODI', 'name' => 'Linux', 'grade' => 4],
        ]
        ]
        @endphp
        <div class="text-center">
            <div class="card m-4">
                <div class="card-header">Name: {{$student['name']}}</div>
                <div class="card-header">ID: {{$student['id']}}</div>
                <div class="card-header">Department: {{$student['department']}}</div>
                <div class="card-header">GPA: {{$student['GPA']}}</div>
            </div>
        </div>
        <table class="table mt-4 border-4">
            <thead>
            <tr>
                <th scope="col">Course ID</th>
                <th scope="col">Course Name</th>
                <th scope="col">Course grade</th></tr>
            </thead>
            <tbody>
            @foreach($student['courses'] as $course)
                <tr>
                    <th>{{$course['code']}}</th>
                    <td>{{$course['name']}}</td>
                    <td>{{$course['grade']}}</td>

                    {{--                    @php($init_price += $item['Quantity'] * $item['Price'])--}}
                </tr>
            @endforeach
            {{--            <td></td>--}}
            {{--            <td></td>--}}
            {{--            <td></td>--}}
            {{--            <td>{{$init_price}}</td>--}}
            </tbody>
        </table>
    </div>
    </div>

    </body>
    </html>


@endsection
