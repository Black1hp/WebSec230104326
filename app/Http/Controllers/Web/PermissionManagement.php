use Spatie\Permission\Models\Role;

$role = Role::findByName('admin'); // Replace 'admin' with your role name
$role->givePermissionTo('edit_users');