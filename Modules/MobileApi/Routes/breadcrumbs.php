<?php

Breadcrumbs::for('mobile-devices.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Mobile Devices', route('mobile-devices.index'));
});
