import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/central/app.css', 
                'resources/css/central/auth.css', 
                'resources/js/central/app.js',
                'resources/css/tenant/app.css',
                'resources/css/tenant/admin.css',
                'resources/css/tenant/teacher.css',
                'resources/css/tenant/auth.css',
                'resources/css/tenant/announcements.css',
                'resources/css/tenant/profile.css',
                'resources/css/tenant/welcome.css',
                'resources/js/tenant/admin.js',
                'resources/js/tenant/announcements.js',
                'resources/js/tenant/app.js',
                'resources/js/tenant/bootstrap.js',
                'resources/js/tenant/categories.js',
                'resources/js/tenant/datefilter.js',
                'resources/js/tenant/navbar.js',
                'resources/js/tenant/profile.js',
                'resources/js/tenant/settings.js',
                'resources/js/tenant/studentpage.js',
                'resources/js/tenant/subscription.js',
                'resources/js/tenant/templates.js',
                'resources/js/tenant/theme.js',
                'resources/js/tenant/users.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
