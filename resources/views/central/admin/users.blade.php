@extends('central.layouts.admin-layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Users</h2>
            <p class="text-secondary">Manage all central users.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-3">
            <h5 class="fw-semibold mb-0">All Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-secondary small">
                        <tr>
                            <th class="fw-medium">Name</th>
                            <th class="fw-medium">Email</th>
                            <th class="fw-medium">Role</th>
                            <th class="fw-medium">Joined</th>
                            <th class="fw-medium text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded">
                                        <i class="bi bi-person text-primary"></i>
                                    </div>
                                    <span class="fw-medium">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="text-secondary">{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleLabel = $user->is_admin ? 'Admin' : 'User';
                                    $roleClass = $user->is_admin ? 'bg-primary bg-opacity-10 text-primary' : 'bg-info bg-opacity-10 text-info';
                                @endphp
                                <span class="badge {{ $roleClass }} px-3 py-2 rounded-pill">{{ $roleLabel }}</span>
                            </td>
                            <td class="text-secondary small">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
