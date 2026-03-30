@if(isset($appearance))
    @php
        $userRole = auth()->check() ? auth()->user()->role : '';
        $isAdmin = $userRole === 'admin';
        $isTeacher = $userRole === 'teacher';
        $isStudent = $userRole === 'student';
        
        $applyToTeacher = ($appearance['applyToTeacher'] ?? '0') == '1';
        $applyToStudent = ($appearance['applyToStudent'] ?? '0') == '1';
        $syncNavToTeacher = ($appearance['syncNavToTeacher'] ?? '1') == '1';
        
        // Branding applies if admin OR (role matches and apply flag is set)
        $shouldApplyBranding = $isAdmin || ($isTeacher && $applyToTeacher) || ($isStudent && $applyToStudent);
        
        // Navigation position applies if admin OR (teacher and sync flag is set)
        $shouldApplyNavPos = $isAdmin || ($isTeacher && $syncNavToTeacher);
        
        $navPos = $appearance['navPos'] ?? 'left';
    @endphp

    @if($shouldApplyBranding)
        <style id="custom-appearance-vars">
            :root {
                @if(($appearance['theme'] ?? 'light') === 'custom')
                    @if(!empty($appearance['customPrimary'])) --teal: {{ $appearance['customPrimary'] }} !important; --primary-color: {{ $appearance['customPrimary'] }} !important; @endif
                    @if(!empty($appearance['customTopbar'])) --topbar-bg: {{ $appearance['customTopbar'] }} !important; @endif
                    @if(!empty($appearance['customSidebar'])) --sidebar-bg: {{ $appearance['customSidebar'] }} !important; @endif
                    @if(!empty($appearance['customSidebarText'])) --sidebar-text: {{ $appearance['customSidebarText'] }} !important; @endif
                    @if(!empty($appearance['customSidebarActive'])) --sidebar-active: {{ $appearance['customSidebarActive'] }} !important; @endif
                    @if(!empty($appearance['customMainBg'])) --bg: {{ $appearance['customMainBg'] }} !important; @endif
                    @if(!empty($appearance['customMainText'])) --text: {{ $appearance['customMainText'] }} !important; @endif
                    @if(!empty($appearance['customSecondaryText'])) --muted: {{ $appearance['customSecondaryText'] }} !important; @endif
                    @if(!empty($appearance['customSurface'])) --surface: {{ $appearance['customSurface'] }} !important; @endif
                @endif
            }
        </style>
    @endif

    <script>
        (function() {
            const savedTheme = "{{ $appearance['theme'] ?? 'light' }}";
            const localTheme = localStorage.getItem('theme');
            const navPos = "{{ $navPos }}";
            const shouldApplyNavPos = {{ $shouldApplyNavPos ? 'true' : 'false' }};
            const shouldApplyBranding = {{ $shouldApplyBranding ? 'true' : 'false' }};
            
            // Apply navigation position
            if (shouldApplyNavPos) {
                const applyNav = () => {
                    const layouts = document.querySelectorAll('.admin-layout');
                    layouts.forEach(layout => {
                        layout.classList.remove('nav-left', 'nav-right', 'nav-top');
                        layout.classList.add('nav-' + navPos);
                    });
                };
                applyNav();
                window.addEventListener('DOMContentLoaded', applyNav);
            }

            if (shouldApplyBranding) {
                // Priority: Local Storage (for immediate toggle feedback) > Saved Database Theme
                let themeToApply = localTheme || savedTheme;

                if (themeToApply === 'system') {
                    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                } else if (themeToApply === 'custom') {
                    document.documentElement.setAttribute('data-theme', 'light');
                } else {
                    document.documentElement.setAttribute('data-theme', themeToApply);
                }
            } else if (localTheme) {
                document.documentElement.setAttribute('data-theme', localTheme);
            }
        })();
    </script>
@else
    <script>
        if (localStorage.getItem('theme')) {
            document.documentElement.setAttribute('data-theme', localStorage.getItem('theme'));
        }
    </script>
@endif

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">











