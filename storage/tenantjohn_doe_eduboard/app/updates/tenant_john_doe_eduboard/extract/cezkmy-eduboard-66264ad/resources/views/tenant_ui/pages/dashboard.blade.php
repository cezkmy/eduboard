@php
    $templateId = tenant('template_id') ?? 1;
    $templatePath = "tenant_ui.templates.template" . $templateId;
@endphp

@if(view()->exists($templatePath))
    @include($templatePath)
@else
    @include('tenant_ui.templates.template1') {{-- Fallback to template 1 --}}
@endif














