<?php

/*
 * Permissions, sidebar menu and page titles in one place.
 *
 * - 'submenus' entries are sidebar headers.
 * - 'locked' => true entries are modules of the private full version: no permissions, shown to every logged-in user
 *   with a lock badge, opening route('preview', $key). 'in_menu' => false keeps one out of the sidebar
 *   (e.g. the Import button on Customers). Seeders and the role-permission screen skip them.
 */
return [
    'separator-booking' => ['title' => 'Operations', 'submenus' => ['customers', 'booking', 'purchase', 'delivery', 'stock-adjustments']],
    'customers' => [
        'title' => 'Customers',
        'icon' => 'users',
        'action' => 'customers.index',
        'pre_assigned_roles' => ['admin', 'owner'],
        'permissions' => [
            'view' => 'customers.index',
            'create' => 'customers.create',
            'edit' => 'customers.edit',
            'delete' => 'customers.destroy',
        ],
    ],
    'booking' => [
        'title' => 'Bookings',
        'icon' => 'book',
        'action' => 'booking.index',
        'pre_assigned_roles' => ['admin', 'owner'],
        'permissions' => [
            'view' => 'booking.index',
            'create' => 'booking.create',
            'edit' => 'booking.edit',
            'delete' => 'booking.destroy',
        ],
    ],
    'purchase' => [
        'title' => 'Purchases',
        'icon' => 'shopping-cart',
        'action' => 'purchase.index',
        'pre_assigned_roles' => ['admin', 'owner'],
        'permissions' => [
            'view' => 'purchase.index',
            'create' => 'purchase.create',
            'edit' => 'purchase.edit',
            'delete' => 'purchase.destroy',
        ],
    ],
    'delivery' => [
        'title' => 'Deliveries',
        'icon' => 'truck',
        'locked' => true,
        'preview' => [
            'summary' => 'Plan daily truck trips: load pending bookings, print a loading sheet, mark each booking delivered or not delivered, collect empties and complete the trip.',
            'points' => [
                'Load / unload pending bookings while the truck is loading - customer search shows their open bookings and empties owed',
                'Printable loading sheet for the driver: one stop per customer, cylinders to deliver and empties to collect',
                '"Truck left" locks the load; "Back to loading" while nothing is marked yet',
                'Delivered / not delivered per booking - not delivered goes back to pending for the next trip, with undo',
                'Empties (and damaged) collected per customer, brand and size',
                'Completing the trip closes it; stock and empties owed update as each booking is marked',
            ],
            'images' => ['delivery-list.png', 'delivery-trip.png', 'loading-sheet.png', 'delivery-empties.png'],
        ],
    ],
    'stock-adjustments' => [
        'title' => 'Stock adjustments',
        'icon' => 'sliders',
        'locked' => true,
        'preview' => [
            'summary' => 'Manual stock changes with a reason, so the godown count always matches what is really there.',
            'points' => [
                'Opening stock when the system starts',
                'Cylinders customers already had before the system (they owe the empties back)',
                'Damaged cylinders found in the godown - kept apart from full and empty until sent back to the company',
                'Count corrections (add or remove) - can\'t remove more than the godown holds',
            ],
            'images' => ['stock-adjustment.png'],
        ],
    ],
    'separator-cylinders' => ['title' => 'Cylinders', 'submenus' => ['cylinder-brands', 'cylinder-types']],
    'cylinder-brands' => [
        'title' => 'Brands',
        'icon' => 'grid',
        'action' => 'cylinder-brands.index',
        'pre_assigned_roles' => ['admin', 'owner'],
        'permissions' => [
            'view' => 'cylinder-brands.index',
            'create' => 'cylinder-brands.create',
            'edit' => 'cylinder-brands.edit',
            'delete' => 'cylinder-brands.destroy',
        ],
    ],
    'cylinder-types' => [
        'title' => 'Types',
        'icon' => 'layers',
        'action' => 'cylinder-types.index',
        'pre_assigned_roles' => ['admin', 'owner'],
        'permissions' => [
            'view' => 'cylinder-types.index',
            'create' => 'cylinder-types.create',
            'edit' => 'cylinder-types.edit',
            'delete' => 'cylinder-types.destroy',
        ],
    ],
    'separator-transport' => ['title' => 'Trucks & drivers', 'submenus' => ['trucks', 'drivers']],
    'trucks' => [
        'title' => 'Trucks',
        'icon' => 'navigation',
        'locked' => true,
        'preview' => [
            'summary' => 'Trucks are learnt from the delivery and purchase forms; this screen keeps the list clean.',
            'points' => [
                'Truck numbers cleaned to one format (GJ 03 AB 1234) on every form',
                'Each truck remembers its last driver, to fill the form faster',
                'Edit a number once and every old trip shows it; set inactive to stop suggesting it',
                'Delete only trucks that were never used',
            ],
            'images' => ['trucks-drivers.png'],
        ],
    ],
    'drivers' => [
        'title' => 'Drivers',
        'icon' => 'user',
        'locked' => true,
        'preview' => [
            'summary' => 'Drivers are learnt from the delivery and purchase forms; this screen keeps the list clean.',
            'points' => [
                'One driver per mobile number; same-name drivers kept apart by mobile',
                'Typing an inactive driver again on a form makes them active again',
                'Edit a name once and every old trip shows it',
                'Delete only drivers that were never used',
            ],
            'images' => ['trucks-drivers.png'],
        ],
    ],
    'separator-reports' => ['title' => 'Reports', 'submenus' => ['stock-report', 'pending-booking-report', 'empties-owed-report', 'customer-history-report', 'driver-report']],
    'stock-report' => [
        'title' => 'Stock',
        'icon' => 'package',
        'action' => 'stock-report.index',
        'pre_assigned_roles' => ['admin', 'owner', 'sub owner'],
        'permissions' => [
            'view' => 'stock-report.index',
        ],
    ],
    'pending-booking-report' => [
        'title' => 'Pending bookings',
        'icon' => 'clock',
        'locked' => true,
        'preview' => [
            'summary' => 'Every open booking in one list, oldest due first, to plan the next trips.',
            'points' => [
                'Pending and on-a-truck bookings, with the truck a booking is loaded on',
                'Filters: status, due by a date, customer (name / mobile / customer no), short stock only',
                'Cylinders needed per type for the listed bookings vs full cylinders in the godown',
            ],
            'images' => ['report-pending-bookings.png'],
        ],
    ],
    'empties-owed-report' => [
        'title' => 'Empties owed',
        'icon' => 'rotate-ccw',
        'locked' => true,
        'preview' => [
            'summary' => 'Which customers still have to give empty cylinders back, per brand and size.',
            'points' => [
                'Owed = full cylinders delivered (+ cylinders they had before the system) - empties collected',
                'Shows who also has an open booking, so the empties can be collected on that delivery',
                'Links to each customer\'s full history',
            ],
            'images' => ['report-empties-owed.png'],
        ],
    ],
    'customer-history-report' => [
        'title' => 'Customer history',
        'icon' => 'user-check',
        'locked' => true,
        'preview' => [
            'summary' => 'One customer\'s bookings, deliveries and empties on a timeline with a running balance.',
            'points' => [
                'Summary per cylinder type: delivered, returned, damaged, still owed, open bookings',
                'Timeline oldest first with "empties owed after" on every row',
                'Date range filter; balances stay all-time',
            ],
            'images' => ['report-customer-history.png'],
        ],
    ],
    'driver-report' => [
        'title' => 'Driver report',
        'icon' => 'truck',
        'locked' => true,
        'preview' => [
            'summary' => 'Trips, deliveries and empties per driver for a date range.',
            'points' => [
                'Per driver: trips, bookings delivered / not delivered, cylinders delivered, empties and damaged collected',
                'Pick a driver to see each of their trips',
                'Same-name drivers kept apart by mobile',
            ],
            'images' => ['report-driver.png'],
        ],
    ],
    'customer-import' => [
        'title' => 'Import customers',
        'icon' => 'upload',
        'locked' => true,
        'in_menu' => false,
        'preview' => [
            'summary' => 'Bring an existing customer list in from Excel / CSV in one go.',
            'points' => [
                'Download a template with the right columns',
                'All or nothing: one bad row and nothing is imported, every bad row listed with its file row number',
                'Mobile numbers cleaned (+91 / 0 / spaces), Excel\'s scientific notation detected',
                'A mobile already used by another customer is a row error (one customer per mobile)',
            ],
            'images' => ['customer-import.png'],
        ],
    ],
    'separator-users' => ['title' => 'Settings', 'submenus' => ['users']],
    'users' => [
        'title' => 'Users',
        'icon' => 'user-plus',
        'action' => 'users.index',
        'pre_assigned_roles' => ['admin', 'owner'],
        'permissions' => [
            'view' => 'users.index',
            'create' => 'users.create',
            'edit' => 'users.edit',
            'delete' => 'users.destroy',
        ],
    ],
];
