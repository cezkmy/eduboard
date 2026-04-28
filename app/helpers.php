<?php

if (!function_exists('tenant_has_version')) {
    /**
     * Check if the current tenant has a specific version or higher.
     *
     * @param string $requiredVersion
     * @return bool
     */
    function tenant_has_version(string $requiredVersion): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            return false;
        }

        $currentVersion = $tenant->system_version ?? config('app.version', 'v1.0.0');
        
        // Remove 'v' or 'V' prefix for comparison
        $current = ltrim($currentVersion, 'vV');
        $required = ltrim($requiredVersion, 'vV');

        return version_compare($current, $required, '>=');
    }
}
