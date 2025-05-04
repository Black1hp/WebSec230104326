@extends('layouts.master')
@section('title', 'Edit User')
@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<style>
    .role-card, .permission-card {
        cursor: pointer;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    .role-card:hover, .permission-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .draggable-container {
        min-height: 150px;
        border: 2px dashed #ccc;
        border-radius: 5px;
        padding: 15px;
        background-color: #f8f9fa;
    }
    .assigned-container {
        background-color: #e8f4ff;
        border-color: #007bff;
    }
    .role-card.admin {
        background-color: #ffeeba;
        border-color: #ffc107;
    }
    .role-card.employee {
        background-color: #d1ecf1;
        border-color: #17a2b8;
    }
    .role-card.customer {
        background-color: #d4edda;
        border-color: #28a745;
    }
    .permission-card {
        background-color: #f5f5f5;
        border-color: #6c757d;
    }
    .permission-card.active {
        background-color: #e1f5fe;
        border-color: #03a9f4;
    }
</style>
<div class="d-flex justify-content-center">
    <div class="row m-4 col-sm-10">
        <form action="{{route('users_save', $user->id)}}" method="post" id="userForm">
            {{ csrf_field() }}
            @foreach($errors->all() as $error)
            <div class="alert alert-danger">
            <strong>Error!</strong> {{$error}}
            </div>
            @endforeach
            <div class="row mb-3">
                <div class="col-12">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control" placeholder="Name" name="name" required value="{{$user->name}}">
                </div>
            </div>

            @can('admin_users')
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Role & Permission Management</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Drag roles and permissions to assign them to this user. Some permissions are automatically granted based on roles.
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Available Roles</h6>
                                        </div>
                                        <div class="card-body draggable-container" id="availableRoles">
                                            @foreach($roles as $role)
                                                @if(!$role->taken)
                                                <div class="card role-card {{ strtolower($role->name) }}" 
                                                     data-role="{{ $role->name }}" 
                                                     data-bs-toggle="tooltip" 
                                                     title="Drag to assign">
                                                    <div class="card-body p-2">
                                                        <h6 class="mb-0">{{ $role->name }}</h6>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Assigned Roles</h6>
                                        </div>
                                        <div class="card-body draggable-container assigned-container" id="assignedRoles">
                                            @foreach($roles as $role)
                                                @if($role->taken)
                                                <div class="card role-card {{ strtolower($role->name) }}" 
                                                     data-role="{{ $role->name }}" 
                                                     data-bs-toggle="tooltip" 
                                                     title="Drag to remove">
                                                    <div class="card-body p-2">
                                                        <h6 class="mb-0">{{ $role->name }}</h6>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Available Permissions</h6>
                                        </div>
                                        <div class="card-body draggable-container" id="availablePermissions">
                                            @foreach($permissions as $permission)
                                                @if(!$permission->taken)
                                                <div class="card permission-card" 
                                                     data-permission="{{ $permission->name }}"
                                                     data-bs-toggle="tooltip" 
                                                     title="Drag to assign">
                                                    <div class="card-body p-2">
                                                        <h6 class="mb-0">{{ $permission->display_name }}</h6>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Assigned Permissions</h6>
                                        </div>
                                        <div class="card-body draggable-container assigned-container" id="assignedPermissions">
                                            @foreach($permissions as $permission)
                                                @if($permission->taken)
                                                <div class="card permission-card active" 
                                                     data-permission="{{ $permission->name }}"
                                                     data-bs-toggle="tooltip" 
                                                     title="Drag to remove">
                                                    <div class="card-body p-2">
                                                        <h6 class="mb-0">{{ $permission->display_name }}</h6>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden inputs to store the selected roles and permissions -->
                            <div id="hiddenInputsContainer">
                                @foreach($roles as $role)
                                    @if($role->taken)
                                    <input type="hidden" name="roles[]" value="{{ $role->name }}" class="role-input">
                                    @endif
                                @endforeach
                                
                                @foreach($permissions as $permission)
                                    @if($permission->taken)
                                    <input type="hidden" name="permissions[]" value="{{ $permission->name }}" class="permission-input">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('profile', ['user' => $user->id]) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Define role permissions map
    const rolePermissionsMap = {
        'Admin': ['admin_users', 'show_users', 'edit_users', 'delete_users', 'edit_products', 'delete_products'],
        'Employee': ['show_users', 'edit_users', 'edit_products'],
        'Customer': []
    };
    
    // Make elements draggable
    $('.role-card, .permission-card').draggable({
        revert: "invalid",
        helper: "clone",
        cursor: "move",
        zIndex: 100
    });
    
    // Make containers droppable
    $('#assignedRoles').droppable({
        accept: '.role-card',
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function(event, ui) {
            handleRoleDrop(ui.draggable, true);
        }
    });
    
    $('#availableRoles').droppable({
        accept: '.role-card',
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function(event, ui) {
            handleRoleDrop(ui.draggable, false);
        }
    });
    
    $('#assignedPermissions').droppable({
        accept: '.permission-card',
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function(event, ui) {
            handlePermissionDrop(ui.draggable, true);
        }
    });
    
    $('#availablePermissions').droppable({
        accept: '.permission-card',
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function(event, ui) {
            handlePermissionDrop(ui.draggable, false);
        }
    });
    
    // Role drop handler
    function handleRoleDrop($item, isAssigning) {
        const role = $item.data('role');
        
        // Clone the item instead of moving it
        const $clone = $item.clone();
        
        if (isAssigning) {
            // Check if user already has a role assigned (one role per user)
            if ($('#assignedRoles .role-card').length > 0 && !$item.hasClass('assigned')) {
                alert('A user can only have one role. Please remove the current role first.');
                return;
            }
            
            // Add to assigned roles
            $clone.addClass('assigned');
            $('#assignedRoles').append($clone);
            
            // Add hidden input for role
            $('#hiddenInputsContainer').append('<input type="hidden" name="roles[]" value="' + role + '" class="role-input">');
            
            // Auto-add permissions based on role
            if (rolePermissionsMap[role]) {
                rolePermissionsMap[role].forEach(function(permName) {
                    // Find the permission in available permissions
                    const $perm = $('#availablePermissions .permission-card[data-permission="' + permName + '"]');
                    if ($perm.length) {
                        handlePermissionDrop($perm, true);
                    }
                });
            }
            
            // Remove from available
            $item.remove();
        } else {
            // Remove from assigned
            $clone.removeClass('assigned');
            $('#availableRoles').append($clone);
            
            // Remove hidden input
            $('#hiddenInputsContainer .role-input[value="' + role + '"]').remove();
            
            // Remove from assigned
            $item.remove();
        }
        
        // Make the clone draggable
        $clone.draggable({
            revert: "invalid",
            helper: "clone",
            cursor: "move",
            zIndex: 100
        });
    }
    
    // Permission drop handler
    function handlePermissionDrop($item, isAssigning) {
        const permission = $item.data('permission');
        const $clone = $item.clone();
        
        if (isAssigning) {
            // Add to assigned permissions
            $clone.addClass('active');
            $('#assignedPermissions').append($clone);
            
            // Add hidden input
            $('#hiddenInputsContainer').append('<input type="hidden" name="permissions[]" value="' + permission + '" class="permission-input">');
            
            // Remove from available
            $item.remove();
        } else {
            // Remove from assigned
            $clone.removeClass('active');
            $('#availablePermissions').append($clone);
            
            // Remove hidden input
            $('#hiddenInputsContainer .permission-input[value="' + permission + '"]').remove();
            
            // Remove from assigned
            $item.remove();
        }
        
        // Make the clone draggable
        $clone.draggable({
            revert: "invalid",
            helper: "clone",
            cursor: "move",
            zIndex: 100
        });
    }
    
    // Allow clicking on cards as an alternative to dragging
    $(document).on('click', '#availableRoles .role-card', function() {
        handleRoleDrop($(this), true);
    });
    
    $(document).on('click', '#assignedRoles .role-card', function() {
        handleRoleDrop($(this), false);
    });
    
    $(document).on('click', '#availablePermissions .permission-card', function() {
        handlePermissionDrop($(this), true);
    });
    
    $(document).on('click', '#assignedPermissions .permission-card', function() {
        handlePermissionDrop($(this), false);
    });
});
</script>
@endsection
