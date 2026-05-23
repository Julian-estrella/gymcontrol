<?php

return [
    'users' => [
        'label' => 'Usuarios',
        'icon' => 'fa-users',
        'route' => 'admin.users.index',
        'active' => 'admin.users.*',
    ],
    'roles' => [
        'label' => 'Roles',
        'icon' => 'fa-user-shield',
        'route' => 'admin.roles.index',
        'active' => 'admin.roles.*',
    ],
    'clients' => [
        'label' => 'Clientes',
        'icon' => 'fa-id-card',
        'route' => 'admin.clients.index',
        'active' => 'admin.clients.*',
    ],
    'trainers' => [
        'label' => 'Entrenadores',
        'icon' => 'fa-person-running',
        'route' => 'admin.trainers.index',
        'active' => 'admin.trainers.*',
    ],
    'classes' => [
        'label' => 'Clases',
        'icon' => 'fa-calendar-check',
        'route' => 'admin.classes.index',
        'active' => 'admin.classes.*',
    ],
    'membership-plans' => [
        'label' => 'Membresias',
        'icon' => 'fa-credit-card',
        'route' => 'admin.membership-plans.index',
        'active' => 'admin.membership-plans.*',
    ],
    'payments' => [
        'label' => 'Pagos',
        'icon' => 'fa-file-invoice-dollar',
        'route' => 'admin.payments.index',
        'active' => 'admin.payments.*',
    ],
];
