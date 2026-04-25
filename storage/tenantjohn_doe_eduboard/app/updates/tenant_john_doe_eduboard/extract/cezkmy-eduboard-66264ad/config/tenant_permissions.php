<?php

return [
    'groups' => [
        'User Management' => [
            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',
            'users.lock' => 'Lock Users',
            'users.bulk_update' => 'Perform Bulk Updates',
            'users.approve' => 'Approve/Reject Pending Users',
            'users.manage_permissions' => 'Manage User Roles & Permissions',
        ],
        'Settings' => [
            'settings.view' => 'View School Settings',
            'settings.edit' => 'Edit School Details (Name, Logo, Colors)',
            'settings.theme' => 'Change System Theme/Appearance',
            'settings.categories' => 'Manage School Categories/Departments',
            'settings.subscription' => 'Manage Subscription & Billing',
        ],
        'Announcements' => [
            'announcements.view_all' => 'View All Announcements',
            'announcements.create' => 'Create New Announcements',
            'announcements.edit_any' => 'Edit Any Announcement',
            'announcements.delete_any' => 'Delete Any Announcement',
            'announcements.pin' => 'Pin/Unpin Announcements',
        ],
        'Interactions' => [
            'interactions.comment' => 'Add Comments to Announcements',
            'interactions.react' => 'React to Announcements',
            'interactions.moderate_comments' => 'Delete/Moderate Comments',
        ],
        'Reports' => [
            'reports.view' => 'View Analytics & Reports',
            'reports.export' => 'Export System Reports',
        ],
        'Templates' => [
            'templates.view' => 'View School Templates',
            'templates.manage' => 'Manage Custom Templates',
        ],
        'Updates' => [
            'version.manage' => 'Apply/Rollback System Version Updates',
        ]
    ]
];
