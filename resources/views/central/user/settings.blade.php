@extends('central.layouts.user-layout')

@section('page-title', 'Settings')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Settings</h2>
            <p class="text-secondary">Configure your school account preferences</p>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active bg-success text-white" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                                <i class="bi bi-gear me-2"></i>General
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link text-success" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button">
                                <i class="bi bi-bell me-2"></i>Notifications
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link text-success" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
                                <i class="bi bi-shield me-2"></i>Security
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link text-success" id="school-tab" data-bs-toggle="tab" data-bs-target="#school" type="button">
                                <i class="bi bi-building me-2"></i>School Info
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <h6 class="mb-0 fw-bold">{{ session('success') }}</h6>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- General Settings -->
        <div class="tab-pane fade show active" id="general">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">General Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.user.settings.update') }}">
                        @csrf
                        <input type="hidden" name="active_tab" value="general">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Language</label>
                                <select class="form-select" name="language">
                                    <option value="en" selected>English</option>
                                    <option value="fil">Filipino</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Timezone</label>
                                <select class="form-select" name="timezone">
                                    <option value="Asia/Manila" selected>Asia/Manila (GMT+8)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Date Format</label>
                                <select class="form-select" name="date_format">
                                    <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Time Format</label>
                                <select class="form-select" name="time_format">
                                    <option value="12" selected>12-hour (AM/PM)</option>
                                    <option value="24">24-hour</option>
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success px-4 rounded-pill">
                                    <i class="bi bi-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notifications Settings -->
        <div class="tab-pane fade" id="notifications">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">Notification Preferences</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.user.settings.update') }}">
                        @csrf
                        <input type="hidden" name="active_tab" value="notifications">
                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3">Email Notifications</h6>
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-2">
                                    <div>
                                        <span class="fw-medium">New announcement published</span>
                                        <p class="small text-secondary mb-0">Get notified when you publish announcements</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="notify_announcement" checked>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-2">
                                    <div>
                                        <span class="fw-medium">Media uploads</span>
                                        <p class="small text-secondary mb-0">Get notified when media files are uploaded</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="notify_media" checked>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-2">
                                    <div>
                                        <span class="fw-medium">Subscription updates</span>
                                        <p class="small text-secondary mb-0">Billing and plan change notifications</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="notify_subscription" checked>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success px-4 rounded-pill">
                                    <i class="bi bi-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="tab-pane fade" id="security">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">Security Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.user.settings.update') }}">
                        @csrf
                        <input type="hidden" name="active_tab" value="security">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-3">
                                    <div>
                                        <h6 class="fw-semibold mb-1">Two-Factor Authentication</h6>
                                        <p class="text-secondary small mb-0">Add an extra layer of security</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-sm">Enable</button>
                                </div>
                                <div class="d-flex align-items-center justify-content-between p-3 bg-secondary bg-opacity-10 rounded mb-3">
                                    <div>
                                        <h6 class="fw-semibold mb-1">Login Alerts</h6>
                                        <p class="text-secondary small mb-0">Get email alerts for new logins</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="login_alerts" checked>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success px-4 rounded-pill">
                                    <i class="bi bi-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- School Info Settings -->
        <div class="tab-pane fade" id="school">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">School Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.user.settings.update') }}">
                        @csrf
                        <input type="hidden" name="active_tab" value="school">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">School Name</label>
                                <input type="text" name="school_name" class="form-control" value="{{ auth()->user()->school_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">School Code</label>
                                <input type="text" class="form-control" value="SCH-2024-001" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">School Address</label>
                                <textarea name="address" class="form-control" rows="3">{{ auth()->user()->address ?? 'Manila, Philippines' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone ?? '+63 123 456 7890' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Website</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->school_domain ? 'http://' . auth()->user()->school_domain : 'https://school.edu.ph' }}" readonly>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success px-4 rounded-pill">
                                    <i class="bi bi-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's an active tab stored in session or as a URL hash
            const activeTab = "{{ session('active_tab') }}";
            if (activeTab) {
                const tabEl = document.querySelector(`#${activeTab}-tab`);
                if (tabEl) {
                    const tab = new bootstrap.Tab(tabEl);
                    tab.show();
                    
                    // Update tab styles (only for settings tabs, not sidebar)
                    document.querySelectorAll('#settingsTab .nav-link').forEach(link => {
                        link.classList.remove('bg-success', 'text-white');
                        link.classList.add('text-success');
                    });
                    tabEl.classList.remove('text-success');
                    tabEl.classList.add('bg-success', 'text-white');
                }
            }

            // Sync tab styles on manual click
            document.querySelectorAll('#settingsTab .nav-link').forEach(tabEl => {
                tabEl.addEventListener('shown.bs.tab', function (event) {
                    document.querySelectorAll('#settingsTab .nav-link').forEach(link => {
                        link.classList.remove('bg-success', 'text-white');
                        link.classList.add('text-success');
                    });
                    event.target.classList.remove('text-success');
                    event.target.classList.add('bg-success', 'text-white');
                });
            });
        });
    </script>
</div>
@endsection



