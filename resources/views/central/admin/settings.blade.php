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
                            <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab">
                                <i class="bi bi-code-slash me-2"></i>API
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
                                <input type="text" class="form-control" name="platform_name" value="EduBoard">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Support Email</label>
                                <input type="email" class="form-control" name="support_email" value="support@eduboard.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Platform Description</label>
                                <textarea class="form-control" name="description" rows="3">Multi-tenant school management platform</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Default Language</label>
                                <select class="form-select" name="language">
                                    <option value="en">English</option>
                                    <option value="fil">Filipino</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Timezone</label>
                                <select class="form-select" name="timezone">
                                    <option value="Asia/Manila">Asia/Manila (GMT+8)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Date Format</label>
                                <select class="form-select" name="date_format">
                                    <option value="Y-m-d">YYYY-MM-DD</option>
                                    <option value="m/d/Y">MM/DD/YYYY</option>
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
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                                <div>
                                    <h6 class="fw-semibold mb-1">Two-Factor Authentication</h6>
                                    <p class="text-secondary small mb-0">Add an extra layer of security to your account</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="twoFactor" style="cursor: pointer;">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                                <div>
                                    <h6 class="fw-semibold mb-1">Session Timeout</h6>
                                    <p class="text-secondary small mb-0">Automatically log out after inactivity</p>
                                </div>
                                <select class="form-select w-auto">
                                    <option>15 minutes</option>
                                    <option>30 minutes</option>
                                    <option selected>1 hour</option>
                                    <option>2 hours</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                                <div>
                                    <h6 class="fw-semibold mb-1">Login Notifications</h6>
                                    <p class="text-secondary small mb-0">Get email alerts for new logins</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="loginAlerts" checked>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <div class="row g-4">
                        <div class="col-12">
                            <h6 class="fw-semibold mb-3">Email Notifications</h6>
                            <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-2">
                                <span>New tenant registration</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-2">
                                <span>Payment received</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-2">
                                <span>Subscription expiring</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded">
                                <span>System updates</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Settings -->
        <div class="tab-pane fade" id="api" role="tabpanel">
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-semibold mb-0">API Settings</h5>
                    <button class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg me-1"></i>Generate New Key
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        API keys are used to authenticate requests to the EduBoard API.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Key</th>
                                    <th>Created</th>
                                    <th>Last Used</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Production</td>
                                    <td>
                                        <code>eduboard_live_••••••••••</code>
                                        <button class="btn btn-link btn-sm p-0 ms-2">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                    <td>2026-01-15</td>
                                    <td>2 hours ago</td>
                                    <td>
                                        <button class="btn btn-link text-danger p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Development</td>
                                    <td>
                                        <code>eduboard_test_••••••••••</code>
                                        <button class="btn btn-link btn-sm p-0 ms-2">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                    <td>2026-02-20</td>
                                    <td>1 day ago</td>
                                    <td>
                                        <button class="btn btn-link text-danger p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



