@extends('central.layouts.admin-layout')

@section('page-title', 'Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <!-- Settings Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                <i class="bi bi-gear me-2"></i>General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="bi bi-shield me-2"></i>Security
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                                <i class="bi bi-bell me-2"></i>Notifications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-primary fw-bold" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                <i class="bi bi-rocket-takeoff me-2"></i>Release Manager
                            </button>
                        </li>
                        <!-- Removed Billing Tab -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="tab-content" id="settingsTabContent">
        <!-- General Settings -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">General Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.admin.settings.general') }}">
                        @csrf
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Platform Name</label>
                                <input type="text" class="form-control" name="platform_name" value="{{ \App\Models\CentralSetting::get('platform_name', 'EduBoard') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Support Email</label>
                                <input type="email" class="form-control" name="support_email" value="{{ \App\Models\CentralSetting::get('support_email', 'support@eduboard.com') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Platform Description</label>
                                <textarea class="form-control" name="description" rows="3">{{ \App\Models\CentralSetting::get('description', 'Multi-tenant school management platform') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Default Language</label>
                                <select class="form-select" name="language">
                                    <option value="en" {{ \App\Models\CentralSetting::get('language') == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="fil" {{ \App\Models\CentralSetting::get('language') == 'fil' ? 'selected' : '' }}>Filipino</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Timezone</label>
                                <select class="form-select" name="timezone">
                                    <option value="Asia/Manila" {{ \App\Models\CentralSetting::get('timezone') == 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (GMT+8)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Date Format</label>
                                <select class="form-select" name="date_format">
                                    <option value="Y-m-d" {{ \App\Models\CentralSetting::get('date_format') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                    <option value="m/d/Y" {{ \App\Models\CentralSetting::get('date_format') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success px-5">
                                    <i class="bi bi-check-circle me-2"></i>Save Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="tab-pane fade" id="security" role="tabpanel">
            <div class="card">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">Security Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.admin.settings.security') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                                    <div>
                                        <h6 class="fw-semibold mb-1">Two-Factor Authentication</h6>
                                        <p class="text-secondary small mb-0">Add an extra layer of security to your account</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="twoFactor" id="twoFactor" style="cursor: pointer;" {{ \App\Models\CentralSetting::get('two_factor') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                                    <div>
                                        <h6 class="fw-semibold mb-1">Login Notifications</h6>
                                        <p class="text-secondary small mb-0">Get email alerts for new logins</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="loginAlerts" id="loginAlerts" {{ \App\Models\CentralSetting::get('login_notifications') == '1' || \App\Models\CentralSetting::get('login_notifications') === null ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success px-5">
                                    <i class="bi bi-shield-check me-2"></i>Save Security Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notifications Settings -->
        <div class="tab-pane fade" id="notifications" role="tabpanel">
            <div class="card">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">Notification Preferences</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.admin.settings.notifications') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3">System Settings & Toggles</h6>
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-2">
                                    <span>New tenant registration (Disables central register page if off)</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="registration_enabled" {{ \App\Models\CentralSetting::get('registration_enabled') == '1' || \App\Models\CentralSetting::get('registration_enabled') === null ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                                    <span>System updates email blast (Allow broadcast signals)</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="system_updates" {{ \App\Models\CentralSetting::get('system_updates_enabled') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success px-5">
                                    <i class="bi bi-bell-check me-2"></i>Update Preferences
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <!-- Release Manager Settings -->
        <div class="tab-pane fade" id="system" role="tabpanel">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="fw-semibold mb-0"><i class="bi bi-rocket-takeoff me-2"></i>System Update Broadcast Manager</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Use this panel to officially broadcast a new software version to your Tenants. Doing so will instantly blast out a formatted "System Update Available" email to every active Tenant Admin, prompting them to log in and apply the patch.
                    </div>
                    
                    <form method="POST" action="{{ route('central.admin.settings.release') }}" class="mt-4">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Version Name <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text text-muted">Version</span>
                                    <input type="text" class="form-control" name="version" placeholder="e.g. 2.0 or 3.1.4" required>
                                </div>
                                <div class="form-text text-muted mb-4">This name will appear exactly as typed in the subject line of your Tenant's emails.</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4 fw-bold" onclick="return confirm('WARNING: This will instantly send an email blast to every single active school in your database! Are you absolutely sure you want to broadcast this release?')">
                            <i class="bi bi-send-fill me-2"></i> Broadcast Update to All Tenants
                        </button>
                    </form>

                    @if(\App\Models\CentralSetting::get('previous_system_version'))
                    <div class="mt-5 pt-4 border-top">
                        <h6 class="text-danger fw-bold mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Danger Zone: Rollback</h6>
                        <p class="text-muted small mb-3">If the last broadcasted version had issues, you can rollback the central system version to the previous one ({{ \App\Models\CentralSetting::get('previous_system_version') }}).</p>
                        <form method="POST" action="{{ route('central.admin.settings.release.rollback') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to rollback to version {{ \App\Models\CentralSetting::get('previous_system_version') }}?')">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Rollback to {{ \App\Models\CentralSetting::get('previous_system_version') }}
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



