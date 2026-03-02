<?php
// Menu Index
Breadcrumbs::for('menu.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Menu ', route('menu.index'));
});

//Menu Edit
Breadcrumbs::for('menu.edit', function ($trail, $menu) {
    $trail->parent('menu.index');
    $trail->push('Edit Menu', route('menu.edit', $menu->id));
});

//Menu Create
Breadcrumbs::for('menu.create', function ($trail) {
    $trail->parent('menu.index');
    $trail->push('Create Menu', route('menu.create'));
});


// Menu > [Menu Name]
Breadcrumbs::for('menu_name', function ($trail, $menu) {
    $trail->parent('menu.index');
    $trail->push($menu->menu_name, route('menu.index'));
});


// Menu Index
Breadcrumbs::for('menu.menus', function ($trail,$menu) {
    $trail->parent('menu_name',$menu);
    $trail->push('Submenu ', route('menu.menus',$menu->id));
});




// Permission Index
Breadcrumbs::for('permission.index', function ($trail,$menu) {
    $trail->parent('menu_name',$menu);
    $trail->push('Permission ', route('menu.permission.index',$menu->id));
});

//Permission Edit
Breadcrumbs::for('permission.edit', function ($trail,$menu,$permission) {
    $trail->parent('menu_name',$menu);
    $trail->push('Edit Permission', route('menu.permission.edit',[$menu->id, $permission->id]));
});

//Permission  Create
Breadcrumbs::for('permission.create', function ($trail,$menu) {
    $trail->parent('menu.index');
    $trail->push('Create Permission', route('menu.permission.create',$menu->id));
});

// Role Index
Breadcrumbs::for('role.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Role ', route('role.index'));
});

//Role Edit
Breadcrumbs::for('role.edit', function ($trail,$role) {
    $trail->parent('role.index');
    $trail->push('Edit Role', route('role.edit', $role->id));
});

//Role  Create
Breadcrumbs::for('role.create', function ($trail) {
    $trail->parent('role.index');
    $trail->push('Create Role', route('role.create'));
});


// Role > [Menu Name]
Breadcrumbs::for('role_name', function ($trail, $role) {
    $trail->parent('role.index');
    $trail->push(ucwords(str_replace('_', ' ',$role->name)), route('role.index'));
});


//Role  Permission
Breadcrumbs::for('rolePermission', function ($trail,$role) {
    $trail->parent('role_name',$role);
    $trail->push('Set RolePermission', route('setPermission',$role->id));
});
