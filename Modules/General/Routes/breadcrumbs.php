<?php
// Building Type Index
Breadcrumbs::for('workFlowCategory.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Work Flow Categories ', route('workFlowCategory.index'));
});

//Building Type Edit
Breadcrumbs::for('workFlowCategory.edit', function ($trail, $workFlowCategory) {
    $trail->parent('workFlowCategory.index');
    $trail->push('Edit Work Flow Category', route('workFlowCategory.edit', $workFlowCategory->id));
});

//Building Type Create
Breadcrumbs::for('workFlowCategory.create', function ($trail) {
    $trail->parent('workFlowCategory.index');
    $trail->push('Create Work Flow Category', route('workFlowCategory.create'));
});

// View
Breadcrumbs::for('workFlowCategory.show', function ($trail) {
    $trail->parent('workFlowCategory.index');
    $trail->push('View');
});


?>