<?php

Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('dashboard'),['icon' => 'fa-home']);

});

Breadcrumbs::for('permissionDenied', function ($trail) {
    $trail->parent('home');
    $trail->push('Permission Denied');

});

// Average Receivables
Breadcrumbs::for('average_receivables', function ($trail) {
    $trail->parent('home');
    $trail->push('Average Receivables', route('averageReceivableUnit'));
});

Breadcrumbs::for('notfound', function ($trail) {
    $trail->parent('home');
    $trail->push('404 Not Found');

});

Breadcrumbs::for('changePassword', function ($trail) {
	$trail->parent('home');
    $trail->push('Change Password', route('change_password'));

});


Breadcrumbs::for('ceo-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('CEO Dashboard', route('dashboard'));
});


Breadcrumbs::for('maintenance-engineer-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance Engineer Dashboard', route('dashboard'));
});

Breadcrumbs::for('facility-manager-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Facility Manager Dashboard', route('dashboard'));
});



Breadcrumbs::for('sales-person-dashboard', function ($trail) {
	$trail->parent('home');
    $trail->push('Sales Person Dashboard', route('dashboard'));
});

Breadcrumbs::for('sales-coordinator-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Sales Coordinator Dashboard', route('dashboard'));
});

Breadcrumbs::for('take-over-executive-dashboard', function ($trail) {
	$trail->parent('home');
    $trail->push('Take Over Executive Dashboard', route('dashboard'));
});

Breadcrumbs::for('maintenance-supervisor-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance Supervisor Dashboard', route('dashboard'));
});
Breadcrumbs::for('maintenance-coordinator-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance Coordinator Dashboard', route('dashboard'));
});

Breadcrumbs::for('take-over-coordinator-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Take Over Coordinator Dashboard', route('dashboard'));
});

Breadcrumbs::for('backoffice-executive-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('BackOffice Executive Dashboard', route('dashboard'));
});
Breadcrumbs::for('are-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('ARE Dashboard', route('dashboard'));
});
Breadcrumbs::for('are-team-lead-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('ARE Team Lead Dashboard', route('dashboard'));
});
Breadcrumbs::for('legal-advisor-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Legal Advisor Dashboard', route('dashboard'));
});
Breadcrumbs::for('backoffice-manager-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('BackOffice Manager Dashboard', route('dashboard'));
});

Breadcrumbs::for('profileView', function ($trail) {
	$trail->parent('home');
    $trail->push('Profile', route('profileView'));
});
Breadcrumbs::for('settings', function ($trail) {
	$trail->parent('home');
    $trail->push('Settings');
});
Breadcrumbs::for('log', function ($trail) {
	$trail->parent('home');
    $trail->push('System Log');
});
Breadcrumbs::for('vacancy_loss', function ($trail) {
    $trail->parent('home');
    $trail->push('Vacancy Loss', route('dashboard'));
});
