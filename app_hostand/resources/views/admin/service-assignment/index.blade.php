@extends('layouts.app')
@section('page-title')
    {{ __('Service Assignment Management') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Service Assignment') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Service Assignment Management') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-users"></i> {{ __('Assign and manage maintenance services to operators') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                      <a href="{{ route('admin.service-assignment.operator-reports') }}" class="btn btn-light me-2">
                                <i class="ti ti-report"></i> {{ __('Operator Reports') }}
                            </a>
                            <button type="button" class="btn btn-light me-2" data-bs-toggle="modal"
                                data-bs-target="#bulkAssignModal">
                                <i class="ti ti-list-check"></i> {{ __('Bulk Assign') }}
                            </button>
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-light">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Properties') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-warning bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-clock text-warning f-24"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ $allRequests->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Total Requests') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-info bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-user-check text-info f-24"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ $maintainers->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Available Operators') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-warning bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-clock text-warning f-24"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ $pendingRequests->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Pending Requests') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-success bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-tools text-success f-24"></i>
                    </div>
                    <h4 class="text-success mb-1">{{ $todayAssigned->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Today\'s Assignments') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-primary bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-calendar text-primary f-24"></i>
                    </div>
                    <h4 class="text-primary mb-1">{{ $tomorrowAssigned->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Tomorrow\'s Assignments') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Services — grouped by day -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-primary">
                            <i class="ti ti-list"></i> {{ __('All Service Requests') }}
                            <small class="text-muted fw-normal ms-2">{{ __('grouped by scheduled date · closest first') }}</small>
                        </h6>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            {{-- From date: show services from this date onward (default today = closest first) --}}
                            <form method="get" action="{{ route('admin.service-assignment.index') }}" class="d-inline-flex align-items-center gap-1">
                                <label class="text-muted small mb-0 me-1">{{ __('From date:') }}</label>
                                <input type="date" name="from_date" id="fromDateInput" class="form-control form-control-sm" style="width: auto;"
                                    value="{{ $fromDate ?? $today->format('Y-m-d') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-filter"></i> {{ __('Apply') }}
                                </button>
                            </form>
                            <span class="text-muted small">|</span>
                            <small class="text-muted me-1">{{ __('Filter:') }}</small>
                            <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                            </select>
                            <select id="assignFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="">{{ __('All Assignments') }}</option>
                                <option value="assigned">{{ __('Assigned') }}</option>
                                <option value="unassigned">{{ __('Unassigned') }}</option>
                            </select>
                            <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="ti ti-x"></i> {{ __('Clear') }}
                            </button>
                            <small id="filterCount" class="text-muted ms-2"></small>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    @if ($allRequests->count() > 0)

                        @php
                            $today      = \Carbon\Carbon::today();
                            $tomorrow   = \Carbon\Carbon::tomorrow();
                        @endphp

                        @foreach ($allRequestsByDate as $dateKey => $groupRequests)
                            @php
                                if ($dateKey === 'no-date') {
                                    $dayLabel      = __('No Date Scheduled');
                                    $dayBadgeClass = 'bg-secondary';
                                    $bannerClass   = 'bg-light border-start border-secondary border-4';
                                    $textClass     = 'text-secondary';
                                    $icon          = 'ti-calendar-off';
                                } else {
                                    $dateObj = \Carbon\Carbon::parse($dateKey)->locale('it');
                                    $dateStr = $dateObj->translatedFormat('l, d M Y');
                                    if ($dateObj->isToday()) {
                                        $dayLabel      = '🟢 ' . __('Today', [], 'italian') . ' — ' . $dateStr;
                                        $dayBadgeClass = 'bg-success';
                                        $bannerClass   = 'bg-success bg-opacity-10 border-start border-success border-4';
                                        $textClass     = 'text-success';
                                        $icon          = 'ti-calendar-check';
                                    } elseif ($dateObj->isTomorrow()) {
                                        $dayLabel      = '🟡 ' . __('Tomorrow', [], 'italian') . ' — ' . $dateStr;
                                        $dayBadgeClass = 'bg-warning';
                                        $bannerClass   = 'bg-warning bg-opacity-10 border-start border-warning border-4';
                                        $textClass     = 'text-warning';
                                        $icon          = 'ti-calendar-event';
                                    } elseif ($dateObj->isFuture()) {
                                        $dayLabel      = '📅 ' . $dateStr;
                                        $dayBadgeClass = 'bg-primary';
                                        $bannerClass   = 'bg-primary bg-opacity-10 border-start border-primary border-4';
                                        $textClass     = 'text-primary';
                                        $icon          = 'ti-calendar';
                                    } else {
                                        $dayLabel      = '📋 ' . $dateStr;
                                        $dayBadgeClass = 'bg-secondary';
                                        $bannerClass   = 'bg-light border-start border-secondary border-4';
                                        $textClass     = 'text-secondary';
                                        $icon          = 'ti-calendar-minus';
                                    }
                                }
                            @endphp

                            <div class="day-group">
                            <!-- Day group header -->
                            <div class="rounded px-3 py-2 mb-2 mt-3 d-flex align-items-center gap-2 {{ $bannerClass }}">
                                <i class="ti {{ $icon }} {{ $textClass }}"></i>
                                <strong class="{{ $textClass }}">{{ $dayLabel }}</strong>
                                <span class="badge {{ $dayBadgeClass }} ms-auto">
                                    {{ $groupRequests->count() }} {{ __('service(s)') }}
                                </span>
                            </div>

                            <div class="table-responsive mb-1">
                                <table class="table table-hover align-middle mb-0 service-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:36px;">
                                                <input type="checkbox" class="form-check-input group-select-all"
                                                    data-group="{{ $dateKey }}">
                                            </th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Property') }}</th>
                                            <th>{{ __('Unit') }}</th>
                                            <th>{{ __('Service Type') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Assigned To') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupRequests as $request)
                                            <tr data-status="{{ $request->status }}" data-assigned="{{ ($request->maintainer_id && $request->maintainers && $request->maintainers->id) ? '1' : '0' }}">
                                                <td>
                                                    <input type="checkbox" name="selected_services[]"
                                                        value="{{ $request->id }}"
                                                        class="form-check-input service-checkbox"
                                                        data-group="{{ $dateKey }}">
                                                </td>
                                                <td>
                                                    @if ($request->arrival_time)
                                                        @php $at = \Carbon\Carbon::parse($request->arrival_time); @endphp
                                                        <span class="fw-semibold">{{ $at->format('M d, Y') }}</span>
                                                        <br>
                                                        <small class="text-muted">{{ $at->format('H:i') }}</small>
                                                    @else
                                                        <small class="text-muted fst-italic">{{ __('Not set') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $request->properties->name ?? 'N/A' }}</strong>
                                                </td>
                                                <td>{{ $request->units->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-light-info">
                                                        {{ $request->types->title ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($request->status == 'pending' && (!$request->maintainers || $request->maintainers->id == 0))
                                                        <span class="badge bg-danger text-white fw-bold text-uppercase px-2 py-1 shadow-sm rounded" style="font-size: 0.85rem;">
                                                            {{ __('To be Assigned') }}
                                                        </span>
                                                    @elseif ($request->status == 'pending')
                                                        <span class="badge bg-warning text-dark text-uppercase px-2 py-1 shadow-sm rounded" style="font-size: 0.85rem;">
                                                            {{ __('Pending') }}
                                                        </span>
                                                    @elseif($request->status == 'in_progress')
                                                        <span class="badge bg-info text-white text-uppercase px-2 py-1 shadow-sm rounded" style="font-size: 0.85rem;">
                                                            {{ __('In Progress') }}
                                                        </span>
                                                    @elseif($request->status == 'completed')
                                                        <span class="badge bg-success text-white text-uppercase px-2 py-1 shadow-sm rounded" style="font-size: 0.85rem;">
                                                            {{ __('Completed') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary text-white text-uppercase px-2 py-1 shadow-sm rounded" style="font-size: 0.85rem;">
                                                            {{ ucfirst($request->status) }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if ($request->maintainers && $request->maintainers->id)
                                                        <small class="text-success">
                                                            <i class="ti ti-user"></i> {{ $request->maintainers->name }}
                                                        </small>
                                                        @if ($request->rider && $request->rider->id)
                                                            <br><small class="text-muted"><i class="ti ti-bike"></i> {{ __('Rider') }}: {{ $request->rider->name }}</small>
                                                        @endif
                                                    @else
                                                        <small class="text-muted">
                                                            <i class="ti ti-user-off"></i> {{ __('Unassigned') }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ Str::limit($request->description, 50) }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        @if ($request->status == 'pending' && (!$request->maintainers || !$request->maintainers->id))
                                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                                data-bs-toggle="modal" data-bs-target="#assignModal"
                                                                data-request-id="{{ $request->id }}"
                                                                data-request-property="{{ $request->properties->name ?? 'N/A' }}"
                                                                data-request-unit="{{ $request->units->name ?? 'N/A' }}"
                                                                data-request-type="{{ $request->types->title ?? 'N/A' }}"
                                                                data-service-type-id="{{ $request->types->id ?? '' }}"
                                                                data-service-date="{{ $request->arrival_time ? \Carbon\Carbon::parse($request->arrival_time)->format('Y-m-d\TH:i') : '' }}">
                                                                <i class="ti ti-user-plus"></i> {{ __('Assign') }}
                                                            </button>
                                                        @elseif($request->status == 'pending' && $request->maintainers && $request->maintainers->id)
                                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                                data-bs-toggle="modal" data-bs-target="#reassignModal"
                                                                data-request-id="{{ $request->id }}"
                                                                data-current-maintainer="{{ $request->maintainers->name }}"
                                                                data-current-rider-id="{{ $request->rider_id ?? 0 }}"
                                                                data-service-date="{{ $request->arrival_time ? \Carbon\Carbon::parse($request->arrival_time)->format('Y-m-d\TH:i') : '' }}">
                                                                <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                                onclick="unassignService({{ $request->id }})">
                                                                <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                                            </button>
                                                        @elseif($request->status == 'in_progress')
                                                            <button type="button" class="btn btn-outline-warning btn-sm"
                                                                data-bs-toggle="modal" data-bs-target="#reassignModal"
                                                                data-request-id="{{ $request->id }}"
                                                                data-current-maintainer="{{ $request->maintainers && $request->maintainers->id ? $request->maintainers->name : 'Unassigned' }}"
                                                                data-current-rider-id="{{ $request->rider_id ?? 0 }}">
                                                                <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                                onclick="unassignService({{ $request->id }})">
                                                                <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                                            </button>
                                                        @endif
                                                        <a href="#" class="btn btn-outline-info btn-sm customModal"
                                                            data-size="lg" data-title="{{ __('View Request Details') }}"
                                                            data-url="{{ route('maintenance-request.show', $request->id) }}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        @endforeach

                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">{{ __('No Service Requests') }}</h5>
                            <p class="text-muted">{{ __('No maintenance requests found in the system.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <!-- Today's and Tomorrow's Assignments -->
    <div class="row">
        <!-- Today's Assignments -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-success">
                        <i class="ti ti-calendar"></i> {{ __('Today\'s Assignments') }} ({{ $today->locale('it')->translatedFormat('d M Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    @if ($todayAssigned->count() > 0)
                        @foreach ($todayAssigned as $assignment)
                            <div class="card mb-2 border-success">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $assignment->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $assignment->units->name ?? 'N/A' }} -
                                                {{ $assignment->types->title ?? 'N/A' }}</small>
                                            <br>
                                            <small class="text-success">
                                                <i class="ti ti-user"></i>
                                                {{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}
                                            </small>
                                            @if ($assignment->rider && $assignment->rider->id)
                                                <br><small class="text-muted"><i class="ti ti-bike"></i> {{ __('Rider') }}: {{ $assignment->rider->name }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">{{ __('Assigned') }}</span>
                                            <br>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($assignment->arrival_time)->format('Y-m-d') }}
</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#reassignModal"
                                            data-request-id="{{ $assignment->id }}"
                                            data-current-maintainer="{{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}"
                                            data-current-rider-id="{{ $assignment->rider_id ?? 0 }}">
                                            <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="unassignService({{ $assignment->id }})">
                                            <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="ti ti-calendar-off" style="font-size: 2rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2 mb-0">{{ __('No assignments for today.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tomorrow's Assignments -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-calendar"></i> {{ __('Tomorrow\'s Assignments') }}
                        ({{ $today->copy()->addDay()->locale('it')->translatedFormat('d M Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    @if ($tomorrowAssigned->count() > 0)
                        @foreach ($tomorrowAssigned as $assignment)
                            <div class="card mb-2 border-info">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $assignment->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $assignment->units->name ?? 'N/A' }} -
                                                {{ $assignment->types->title ?? 'N/A' }}</small>
                                            <br>
                                            <small class="text-info">
                                                <i class="ti ti-user"></i>
                                                {{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}
                                            </small>
                                            @if ($assignment->rider && $assignment->rider->id)
                                                <br><small class="text-muted"><i class="ti ti-bike"></i> {{ __('Rider') }}: {{ $assignment->rider->name }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-info">{{ __('Assigned') }}</span>
                                            <br>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($assignment->request_date)->format('H:i') }}</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#reassignModal"
                                            data-request-id="{{ $assignment->id }}"
                                            data-current-maintainer="{{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}"
                                            data-current-rider-id="{{ $assignment->rider_id ?? 0 }}">
                                            <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="unassignService({{ $assignment->id }})">
                                            <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="ti ti-calendar-off" style="font-size: 2rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2 mb-0">{{ __('No assignments for tomorrow.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Assign Service Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Assign Service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.service-assignment.assign') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="maintenance_request_id" id="assignRequestId">
                    <input type="hidden" id="assignServiceTypeId">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Property') }}</label>
                            <input type="text" class="form-control" id="assignProperty" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Unit') }}</label>
                            <input type="text" class="form-control" id="assignUnit" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Service Type') }}</label>
                            <input type="text" class="form-control" id="assignServiceType" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Assign to Operator') }}</label>
                            <select name="maintainer_id" id="assignMaintainerSelect" class="form-control" required>
                                <option value="">{{ __('Loading compatible operators...') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Rider (optional)') }}</label>
                            <select name="rider_id" id="assignRiderSelect" class="form-control">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($maintainers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('For deliveries e.g. laundry at apartments') }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Service Date & Time') }}</label>
                            <!-- datetime-local so operator can see date + time -->
                            <input type="datetime-local" id="assignServiceDate" name="assigned_date"
                                class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="{{ __('Optional notes for the operator...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Assign Service') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reassign Service Modal -->
<div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Reassign Service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.service-assignment.reassign') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="maintenance_request_id" id="reassignRequestId">

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i> {{ __('Currently assigned to:') }} <strong
                            id="currentMaintainer"></strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('New Operator') }}</label>
                            <select name="new_maintainer_id" class="form-control" required>
                                <option value="">{{ __('Select New Operator') }}</option>
                                @foreach ($maintainers as $maintainer)
                                    <option value="{{ $maintainer->id }}">{{ $maintainer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Rider (optional)') }}</label>
                            <select name="rider_id" class="form-control">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($maintainers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('New Service Date') }}</label>
                            <input type="datetime-local" id="reassignServiceDate" name="new_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Reassignment Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('Reason for reassignment...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('Reassign Service') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Assign Services') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.service-assignment.bulk-assign') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i>
                        {{ __('Select services from the table above, then assign them to an operator.') }}
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Operator') }}</label>
                            <select name="maintainer_id" class="form-control" required>
                                <option value="">{{ __('Select Operator') }}</option>
                                @foreach ($maintainers as $maintainer)
                                    <option value="{{ $maintainer->id }}">{{ $maintainer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Rider (optional)') }}</label>
                            <select name="rider_id" class="form-control">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($maintainers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Assignment Date') }}</label>
                            <input type="date" name="assigned_date" class="form-control"
                                value="{{ $today->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="{{ __('Notes for all selected services...') }}"></textarea>
                    </div>

                    <div id="selectedServicesList" class="d-none">
                        <h6>{{ __('Selected Services:') }}</h6>
                        <div id="selectedServicesDisplay"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="bulkAssignBtn"
                        disabled>{{ __('Assign Selected Services') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-page')
    <script>
        // helper to format current datetime for datetime-local input (YYYY-MM-DDTHH:MM)
        function nowLocalDatetime() {
            function pad(n) {
                return n < 10 ? '0' + n : n;
            }
            var d = new Date();
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) +
                'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        }

        // Assign Modal
        $('#assignModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget).closest('[data-bs-toggle="modal"]');
            var requestId = button.data('request-id');
            var property = button.data('request-property');
            var unit = button.data('request-unit');
            var serviceType = button.data('request-type');
            var serviceTypeId = button.data('service-type-id');
            var serviceDate = button.data('service-date'); // expected in 'YYYY-MM-DDTHH:MM' if provided

            $('#assignRequestId').val(requestId);
            $('#assignProperty').val(property);
            $('#assignUnit').val(unit);
            $('#assignServiceType').val(serviceType);
            $('#assignServiceTypeId').val(serviceTypeId);

            // Set the service date & time (datetime-local expects 'YYYY-MM-DDTHH:MM')
            if (serviceDate && serviceDate !== '') {
                $('#assignServiceDate').val(serviceDate);
            } else {
                // fallback to current datetime (rounded to minute)
                $('#assignServiceDate').val(nowLocalDatetime());
            }

            // Load compatible maintainers
            loadCompatibleMaintainers(serviceTypeId);
        });

        function loadCompatibleMaintainers(serviceTypeId) {
            if (!serviceTypeId) {
                $('#assignMaintainerSelect').html('<option value="">{{ __('No service type selected') }}</option>');
                return;
            }

            $('#assignMaintainerSelect').html('<option value="">{{ __('Loading compatible operators...') }}</option>');

            $.ajax({
                url: '{{ route('admin.service-assignment.compatible-maintainers') }}',
                method: 'GET',
                data: {
                    service_type_id: serviceTypeId
                },
                success: function(response) {
                    var select = $('#assignMaintainerSelect');
                    select.html('<option value="">{{ __('Select Operator') }}</option>');

                    if (response.maintainers && response.maintainers.length > 0) {
                        response.maintainers.forEach(function(maintainer) {
                            select.append('<option value="' + maintainer.id + '">' + maintainer.name +
                                ' (' + maintainer.type + ')</option>');
                        });
                    } else {
                        select.html('<option value="">{{ __('No operators available') }}</option>');
                    }
                },
                error: function() {
                    $('#assignMaintainerSelect').html(
                        '<option value="">{{ __('Error loading operators') }}</option>');
                }
            });
        }

        // Reassign Modal
        $('#reassignModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget).closest('[data-bs-toggle="modal"]');
            var requestId = button.data('request-id');
            var currentMaintainer = button.data('current-maintainer');
            var currentRiderId = button.data('current-rider-id');
            var serviceDate = button.data('service-date'); // full format (YYYY-MM-DDTHH:MM)

            $('#reassignRequestId').val(requestId);
            $('#currentMaintainer').text(currentMaintainer);
            $('#reassignModal select[name="rider_id"]').val(currentRiderId && currentRiderId != '0' ? String(currentRiderId) : '');

            // ⭐ Set date + time directly
            if (serviceDate) {
                $('#reassignServiceDate').val(serviceDate);
            }
        });


        // Per-group "select all" checkbox
        $(document).on('change', '.group-select-all', function() {
            var group = $(this).data('group');
            $('.service-checkbox[data-group="' + group + '"]').prop('checked', $(this).is(':checked'));
            updateSelectedServices();
        });

        // Individual Checkboxes
        $(document).on('change', '.service-checkbox', function() {
            updateSelectedServices();
        });

        function updateSelectedServices() {
            var selectedServices = $('.service-checkbox:checked');
            var selectedIds = [];

            selectedServices.each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length > 0) {
                $('#selectedServicesList').removeClass('d-none');
                $('#selectedServicesDisplay').html('<span class="badge bg-primary me-1">' + selectedIds.length +
                    ' services selected</span>');
                $('#bulkAssignBtn').prop('disabled', false);

                // Add hidden inputs for selected services
                $('input[name="service_ids[]"]').remove();
                selectedIds.forEach(function(id) {
                    $('#bulkAssignModal form').append('<input type="hidden" name="service_ids[]" value="' + id +
                        '">');
                });
            } else {
                $('#selectedServicesList').addClass('d-none');
                $('#bulkAssignBtn').prop('disabled', true);
                $('input[name="service_ids[]"]').remove();
            }
        }

        function unassignService(requestId) {
            if (confirm('{{ __('Are you sure you want to unassign this service?') }}')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.service-assignment.unassign') }}';

                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                var requestIdInput = document.createElement('input');
                requestIdInput.type = 'hidden';
                requestIdInput.name = 'maintenance_request_id';
                requestIdInput.value = requestId;

                form.appendChild(csrfToken);
                form.appendChild(requestIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Filter functionality
        $('#statusFilter, #assignFilter').change(function() {
            filterTable();
        });

        // Clear filters
        $('#clearFilters').click(function() {
            $('#statusFilter').val('');
            $('#assignFilter').val('');
            filterTable();
        });

        function filterTable() {
            var statusFilter = $('#statusFilter').val();
            var assignFilter = $('#assignFilter').val();

            // Show all day-groups first so tr:visible counts correctly (rows inside hidden parents are not :visible)
            $('.day-group').show();

            $('.service-table tbody tr').each(function() {
                var row = $(this);
                var rowStatus = row.data('status');
                var rowAssigned = String(row.data('assigned') || '0');

                var showRow = true;

                // Status filter (use data-status so it works in any language)
                if (statusFilter && rowStatus !== statusFilter) {
                    showRow = false;
                }

                // Assignment filter (use data-assigned: 1 = assigned, 0 = unassigned)
                if (assignFilter === 'assigned' && rowAssigned !== '1') {
                    showRow = false;
                } else if (assignFilter === 'unassigned' && rowAssigned !== '0') {
                    showRow = false;
                }

                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });

            // Hide day groups that have no visible rows (count is correct now because parents are visible)
            $('.day-group').each(function() {
                var visible = $(this).find('.service-table tbody tr:visible').length;
                $(this).toggle(visible > 0);
            });

            updateRowCount();
        }

        function updateRowCount() {
            var visibleRows = $('.service-table tbody tr:visible').length;
            var totalRows = $('.service-table tbody tr').length;

            if (visibleRows === totalRows) {
                $('#filterCount').text('');
            } else {
                $('#filterCount').text('(' + visibleRows + ' of ' + totalRows + ')');
            }
        }
    </script>
@endpush
