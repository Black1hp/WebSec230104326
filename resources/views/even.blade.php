<<<<<<< HEAD
@extends('layouts.app')
@section('title', 'Event Numbers')
=======
@extends('layouts.master')
@section('title', 'Prime Numbers')
>>>>>>> Midterm-v2
@section('content')
    <div class="card m-4">
        <div class="card-header">Even Numbers</div>
        <div class="card-body">
<<<<<<< HEAD
            @foreach (range(1, 100) as $i)
                @if($i%2 == 0)
                    <span class="badge bg-primary">{{$i}}</span>
                @else
                    <span class="badge bg-secondary">{{$i}}</span>
                @endif
            @endforeach
        </div>
    </div>

@endsection

=======
            <table>
                @foreach (range(1, 100) as $i)
                    @if($i%2==0)
                        <span class="badge bg-primary m-1">{{$i}}&nbsp;</span>
                    @else
                        <span class="badge bg-secondary m-1">{{$i}}&nbsp;</span>
                    @endif
                @endforeach
            </table>
        </div>
    </div>
@endsection
>>>>>>> Midterm-v2
