@extends('layouts.master')
@section('title', 'Users')
@section('content')
    <div class="row mt-2">
        <div class="col col-10">
            <h1>Users</h1>
        </div>
        <div class="col col-2">
            @role('Admin')
            <a href="{{route('create_employee')}}" class="btn btn-success form-control">Create Employee</a>
            @endrole
        </div>
    </div>

    <div class="alert alert-info">
        <p class="mb-0"><i class="fas fa-info-circle"></i> Note: Your account ({{ auth()->user()->name }}) is not shown in this list.</p>
    </div>

    <form>
        <div class="row">
            <div class="col col-sm-2">
                <input name="keywords" type="text"  class="form-control" placeholder="Search Keywords" value="{{ request()->keywords }}" />
            </div>
            <div class="col col-sm-1">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            <div class="col col-sm-1">
                <a href="{{ route('users') }}" class="btn btn-danger">Reset</a>
            </div>
        </div>
    </form>

    <div class="card mt-2">
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Roles</th>
                    @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Employee'))
                        <th scope="col">Credit Balance</th>
                    @endif
                    <th scope="col"></th>
                </tr>
                </thead>
                @foreach($users as $user)
                    <tr>
                        <td scope="col">{{$user->id}}</td>
                        <td scope="col">{{$user->name}}</td>
                        <td scope="col">{{$user->email}}</td>
                        <td scope="col">
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary">{{$role->name}}</span>
                            @endforeach
                        </td>
                        @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Employee'))
                            <td scope="col">
                                @if($user->hasRole('Customer'))
                                    <span class="badge bg-success">${{ number_format($user->credit, 2) }}</span>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        @endif
                        <td scope="col">
                            @can('edit_users')
                                <a class="btn btn-primary" href='{{route('users_edit', [$user->id])}}'>Edit</a>
                            @endcan
                            @can('admin_users')
                                <a class="btn btn-primary" href='{{route('edit_password', [$user->id])}}'>Change Password</a>
                            @endcan
                            @if($user->hasRole('Customer'))
                                <a class="btn btn-info" href='{{route('user_purchases', [$user->id])}}'>Purchases</a>
                                @if(auth()->user()->hasPermissionTo('manage_sales'))
                                    <form action='{{route('give_gift', [$user->id])}}' method='POST' style='display:inline;'>
                                        @csrf
                                        <button type='submit' class='btn btn-success'>Give a Gift</button>
                                    </form>
                                @endif
                                @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Employee'))
                                    <a class="btn btn-success" href='{{route('charge_credit', [$user->id])}}'>Charge Credit</a>
                                @endif
                            @endif
                            @can('delete_users')
                                @if(!($user->hasRole('Admin') && auth()->user()->hasRole('Admin')))
                                    <a class="btn btn-danger" href='#' onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">Delete</a>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete user <span id="deleteUserName" class="fw-bold"></span>?
                    <p class="text-danger mt-2">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete User</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('confirmDeleteBtn').href = '{{ route('users_delete', ['user' => '__id__']) }}'.replace('__id__', userId);
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>

@endsection
