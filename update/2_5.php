<?php

// Permessi avanzati
$groups = Auth\Group::all();

$array = [];
foreach ($groups as $group) {
    $array[$group->id] = [
        'permission_level' => 'rw',
    ];
}

$plugins = Models\Plugin::all();
foreach ($plugins as $element) {
    $element->groups()->sync($array);
}

$widgets = Widgets\Widget::all();
foreach ($widgets as $element) {
    $element->groups()->sync($array);
}

$segments = Modules\Segment::all();
foreach ($segments as $element) {
    $element->groups()->sync($array);
}

$prints = Models\Template::all();
foreach ($prints as $element) {
    $element->groups()->sync($array);
}

$admin = Auth\Group::where('nome', 'Amministratori')->first();
$modules = Modules\Module::all();
foreach ($modules as $element) {
    $element->groups()->syncWithoutDetaching([
        $admin->id => [
            'permission_level' => 'rw',
        ],
    ]);
}
