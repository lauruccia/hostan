@extends('layouts.app')

@section('page-title')
    {{ __('Service Price List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Service Price List') }}</li>
@endsection

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2 text-white">
                            <i class="ti ti-currency-euro me-2"></i>{{ __('Service & Maintenance Price Lists') }}
                        </h3>
                        <p class="text-white-50 mb-0">
                            {{ __('Manage service price templates and assign them to property owners') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <span class="badge bg-dark bg-opacity-50 text-white border-0 px-3 py-2 shadow-sm">
                                <i class="ti ti-template me-1"></i>{{ __('Step 1: Templates') }}
                            </span>
                            <span class="badge bg-dark bg-opacity-50 text-white border-0 px-3 py-2 shadow-sm">
                                <i class="ti ti-users me-1"></i>{{ __('Step 2: Assignments') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step 1: Service Price Templates -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom pb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">
                            <span class="badge bg-primary bg-opacity-10 text-primary me-2">1</span>
                            {{ __('Service Price Templates') }}
                        </h5>
                        <p class="text-muted mb-0 small">
                            <i class="ti ti-info-circle me-1"></i>
                            {{ __('Create price templates for each service type. These will be used to assign prices to owners.') }}
                        </p>
                    </div>
                    <div class="avtar bg-primary bg-opacity-10">
                        <i class="ti ti-template text-primary f-20"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Template Form -->
                <div class="card border mb-4" id="templateFormCard">
                    <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">
                            <i class="ti ti-plus-circle me-2 text-primary"></i>
                            <span id="templateFormTitle">{{ __('Create New Template') }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('service-price-list.store') }}" id="templateForm">
                            @csrf
                            <input type="hidden" name="is_template" value="1">
                            <input type="hidden" name="edit_id" id="template_edit_id" value="">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        {{ __('Service Type') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="service_type_id" id="template_service_type_id" class="form-select" required>
                                        <option value="">{{ __('Select Service Type') }}</option>
                                        @foreach($serviceTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6"></div>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">
                                        {{ __('Price List 1') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="ti ti-currency-euro"></i>
                                        </span>
                                        <input type="number" name="price_1" id="template_price_1" step="0.01" min="0" 
                                               class="form-control" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">
                                        {{ __('Price List 2') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="ti ti-currency-euro"></i>
                                        </span>
                                        <input type="number" name="price_2" id="template_price_2" step="0.01" min="0" 
                                               class="form-control" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">
                                        {{ __('Price List 3') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="ti ti-currency-euro"></i>
                                        </span>
                                        <input type="number" name="price_3" id="template_price_3" step="0.01" min="0" 
                                               class="form-control" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">
                                        {{ __('Price List 4') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="ti ti-currency-euro"></i>
                                        </span>
                                        <input type="number" name="price_4" id="template_price_4" step="0.01" min="0" 
                                               class="form-control" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">
                                        {{ __('Price List 5') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="ti ti-currency-euro"></i>
                                        </span>
                                        <input type="number" name="price_5" id="template_price_5" step="0.01" min="0" 
                                               class="form-control" placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="w-100">
                                        <button type="submit" class="btn btn-primary w-100 mb-2">
                                            <i class="ti ti-device-floppy me-1"></i>{{ __('Save') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary w-100" id="resetTemplateForm">
                                            <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Templates Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <i class="ti ti-tag me-2"></i>{{ __('Service Type') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 1') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 2') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 3') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 4') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 5') }}
                                </th>
                                <th class="border-0 text-center" style="width: 100px;">
                                    <i class="ti ti-settings me-2"></i>{{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avtar avtar-s bg-primary bg-opacity-10 me-2">
                                                <i class="ti ti-tag text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $template->serviceType->title ?? 'N/A' }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark px-3 py-2">
                                            {{ priceFormat($template->price_1) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark px-3 py-2">
                                            {{ priceFormat($template->price_2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark px-3 py-2">
                                            {{ priceFormat($template->price_3) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark px-3 py-2">
                                            {{ priceFormat($template->price_4) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark px-3 py-2">
                                            {{ priceFormat($template->price_5) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary edit-template" 
                                                data-id="{{ $template->id }}"
                                                data-service="{{ $template->service_type_id }}"
                                                data-p1="{{ $template->price_1 }}"
                                                data-p2="{{ $template->price_2 }}"
                                                data-p3="{{ $template->price_3 }}"
                                                data-p4="{{ $template->price_4 }}"
                                                data-p5="{{ $template->price_5 }}"
                                                title="{{ __('Edit Template') }}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="avtar bg-light mb-3">
                                                <i class="ti ti-inbox text-muted f-24"></i>
                                            </div>
                                            <p class="text-muted mb-0">{{ __('No price templates found.') }}</p>
                                            <small class="text-muted">{{ __('Create your first template above.') }}</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Assign Price Lists to Owners -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom pb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1">
                            <span class="badge bg-success bg-opacity-10 text-success me-2">2</span>
                            {{ __('Assign Price Lists to Owners') }}
                        </h5>
                        <p class="text-muted mb-0 small">
                            <i class="ti ti-info-circle me-1"></i>
                            {{ __('Assign price templates to property owners and select the active price list.') }}
                        </p>
                    </div>
                    <div class="avtar bg-success bg-opacity-10">
                        <i class="ti ti-users text-success f-20"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Assignment Form -->
                <div class="card border mb-4" id="assignmentFormCard">
                    <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">
                            <i class="ti ti-user-plus me-2 text-success"></i>
                            <span id="assignmentFormTitle">{{ __('Assign to Owner') }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('service-price-list.store') }}" id="assignmentForm">
                            @csrf
                            <input type="hidden" name="is_template" value="0">
                            <input type="hidden" name="edit_id" id="assignment_edit_id" value="">
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        {{ __('Service Type') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="service_type_id" id="assignment_service_type_id" class="form-select" required>
                                        <option value="">{{ __('Select Service Type') }}</option>
                                        @foreach($serviceTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->title }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <i class="ti ti-info-circle me-1"></i>
                                        {{ __('Select a service type that has a price template') }}
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        {{ __('Property Owner') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="owner_id" id="assignment_owner_id" class="form-select" required>
                                        <option value="">{{ __('Select Owner') }}</option>
                                        @foreach($owners as $owner)
                                            <option value="{{ $owner->id }}">{{ $owner->first_name }} {{ $owner->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        {{ __('Active Price List') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="assigned_price_list" id="assignment_assigned_price_list" class="form-select" required>
                                        <option value="1">{{ __('Price List 1') }}</option>
                                        <option value="2">{{ __('Price List 2') }}</option>
                                        <option value="3">{{ __('Price List 3') }}</option>
                                        <option value="4">{{ __('Price List 4') }}</option>
                                        <option value="5">{{ __('Price List 5') }}</option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="ti ti-info-circle me-1"></i>
                                        {{ __('Which price list is currently active') }}
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Template Prices Display -->
                            <div class="alert alert-info border-0 mb-4" id="templatePricesAlert" style="display: none;">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ti ti-info-circle me-2"></i>
                                    <strong class="mb-0">{{ __('Template Prices:') }}</strong>
                                </div>
                                <div class="row g-2 mt-2" id="template_prices_display">
                                    <div class="col-md-2">
                                        <div class="card border bg-white">
                                            <div class="card-body p-2 text-center">
                                                <small class="text-muted d-block mb-1">{{ __('Price 1') }}</small>
                                                <strong class="text-primary" id="display_price_1">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card border bg-white">
                                            <div class="card-body p-2 text-center">
                                                <small class="text-muted d-block mb-1">{{ __('Price 2') }}</small>
                                                <strong class="text-primary" id="display_price_2">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card border bg-white">
                                            <div class="card-body p-2 text-center">
                                                <small class="text-muted d-block mb-1">{{ __('Price 3') }}</small>
                                                <strong class="text-primary" id="display_price_3">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card border bg-white">
                                            <div class="card-body p-2 text-center">
                                                <small class="text-muted d-block mb-1">{{ __('Price 4') }}</small>
                                                <strong class="text-primary" id="display_price_4">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="card border bg-white">
                                            <div class="card-body p-2 text-center">
                                                <small class="text-muted d-block mb-1">{{ __('Price 5') }}</small>
                                                <strong class="text-primary" id="display_price_5">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="ti ti-check me-1"></i>{{ __('Assign to Owner') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetAssignmentForm">
                                    <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Assignments Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <i class="ti ti-tag me-2"></i>{{ __('Service Type') }}
                                </th>
                                <th class="border-0">
                                    <i class="ti ti-user me-2"></i>{{ __('Owner') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 1') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 2') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 3') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 4') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-currency-euro me-2"></i>{{ __('Price 5') }}
                                </th>
                                <th class="border-0 text-center">
                                    <i class="ti ti-star me-2"></i>{{ __('Active') }}
                                </th>
                                <th class="border-0 text-center" style="width: 100px;">
                                    <i class="ti ti-settings me-2"></i>{{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avtar avtar-s bg-primary bg-opacity-10 me-2">
                                                <i class="ti ti-tag text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $assignment->serviceType->title ?? 'N/A' }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avtar avtar-s bg-success bg-opacity-10 me-2">
                                                <i class="ti ti-user text-success"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $assignment->owner->first_name ?? '' }} {{ $assignment->owner->last_name ?? '' }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $assignment->assigned_price_list == 1 ? 'bg-success text-white' : 'bg-light text-dark' }} px-3 py-2">
                                            {{ priceFormat($assignment->price_1) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $assignment->assigned_price_list == 2 ? 'bg-success text-white' : 'bg-light text-dark' }} px-3 py-2">
                                            {{ priceFormat($assignment->price_2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $assignment->assigned_price_list == 3 ? 'bg-success text-white' : 'bg-light text-dark' }} px-3 py-2">
                                            {{ priceFormat($assignment->price_3) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $assignment->assigned_price_list == 4 ? 'bg-success text-white' : 'bg-light text-dark' }} px-3 py-2">
                                            {{ priceFormat($assignment->price_4) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $assignment->assigned_price_list == 5 ? 'bg-success text-white' : 'bg-light text-dark' }} px-3 py-2">
                                            {{ priceFormat($assignment->price_5) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary px-3 py-2">
                                            <i class="ti ti-star me-1"></i>{{ __('Price List') }} {{ $assignment->assigned_price_list }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-success edit-assignment" 
                                                data-id="{{ $assignment->id }}"
                                                data-service="{{ $assignment->service_type_id }}"
                                                data-owner="{{ $assignment->owner_id }}"
                                                data-assigned="{{ $assignment->assigned_price_list }}"
                                                title="{{ __('Edit Assignment') }}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="avtar bg-light mb-3">
                                                <i class="ti ti-inbox text-muted f-24"></i>
                                            </div>
                                            <p class="text-muted mb-0">{{ __('No assignments found.') }}</p>
                                            <small class="text-muted">{{ __('Assign a price list to an owner above.') }}</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    .avtar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
    }
    .avtar-s {
        width: 2rem;
        height: 2rem;
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    #templateFormCard, #assignmentFormCard {
        border-left: 4px solid;
    }
    #templateFormCard {
        border-left-color: #667eea;
    }
    #assignmentFormCard {
        border-left-color: #10b981;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template Form Handlers
    document.querySelectorAll('.edit-template').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('template_service_type_id').value = this.dataset.service;
            document.getElementById('template_price_1').value = this.dataset.p1;
            document.getElementById('template_price_2').value = this.dataset.p2;
            document.getElementById('template_price_3').value = this.dataset.p3;
            document.getElementById('template_price_4').value = this.dataset.p4;
            document.getElementById('template_price_5').value = this.dataset.p5;
            document.getElementById('template_edit_id').value = this.dataset.id;
            
            // Update form title
            document.getElementById('templateFormTitle').textContent = '{{ __("Edit Template") }}';
            
            // Change form action to update
            const form = document.getElementById('templateForm');
            form.setAttribute('action', '{{ route("service-price-list.update", ":id") }}'.replace(':id', this.dataset.id));
            // Add method override
            if (!form.querySelector('input[name="_method"]')) {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
            }
            
            // Scroll to form with smooth animation
            document.getElementById('templateFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('templateFormCard').style.animation = 'pulse 0.5s';
        });
    });

    // Auto-load template when service type is selected
    const templateServiceSelect = document.getElementById('template_service_type_id');
    templateServiceSelect.addEventListener('change', function() {
        const serviceTypeId = this.value;
        const form = document.getElementById('templateForm');
        
        if (!serviceTypeId) {
            // Reset form if no service selected
            document.getElementById('template_edit_id').value = '';
            document.getElementById('templateFormTitle').textContent = '{{ __("Create New Template") }}';
            form.setAttribute('action', '{{ route("service-price-list.store") }}');
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
            // Clear price fields
            for (let i = 1; i <= 5; i++) {
                document.getElementById('template_price_' + i).value = '';
            }
            return;
        }

        // Fetch existing template for this service type
        fetch('{{ route("service-price-list.get-templates") }}?service_type_id=' + serviceTypeId)
            .then(response => response.json())
            .then(data => {
                if (data.template && data.template.id) {
                    // Template exists - populate form and switch to edit mode
                    document.getElementById('template_edit_id').value = data.template.id;
                    document.getElementById('template_price_1').value = parseFloat(data.template.price_1).toFixed(2);
                    document.getElementById('template_price_2').value = parseFloat(data.template.price_2).toFixed(2);
                    document.getElementById('template_price_3').value = parseFloat(data.template.price_3).toFixed(2);
                    document.getElementById('template_price_4').value = parseFloat(data.template.price_4).toFixed(2);
                    document.getElementById('template_price_5').value = parseFloat(data.template.price_5).toFixed(2);
                    
                    // Update form title
                    document.getElementById('templateFormTitle').textContent = '{{ __("Edit Template") }}';
                    
                    // Change form action to update
                    form.setAttribute('action', '{{ route("service-price-list.update", ":id") }}'.replace(':id', data.template.id));
                    // Add method override
                    if (!form.querySelector('input[name="_method"]')) {
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);
                    }
                    
                    // Show visual feedback
                    document.getElementById('templateFormCard').style.animation = 'pulse 0.5s';
                } else {
                    // No template exists - keep in create mode
                    document.getElementById('template_edit_id').value = '';
                    document.getElementById('templateFormTitle').textContent = '{{ __("Create New Template") }}';
                    form.setAttribute('action', '{{ route("service-price-list.store") }}');
                    const methodInput = form.querySelector('input[name="_method"]');
                    if (methodInput) {
                        methodInput.remove();
                    }
                    // Clear price fields
                    for (let i = 1; i <= 5; i++) {
                        document.getElementById('template_price_' + i).value = '';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

    // Reset Template Form
    document.getElementById('resetTemplateForm').addEventListener('click', function() {
        document.getElementById('templateForm').reset();
        document.getElementById('template_edit_id').value = '';
        document.getElementById('templateFormTitle').textContent = '{{ __("Create New Template") }}';
        const form = document.getElementById('templateForm');
        form.setAttribute('action', '{{ route("service-price-list.store") }}');
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) {
            methodInput.remove();
        }
    });

    // Load template prices when service type is selected in assignment form
    const assignmentServiceSelect = document.getElementById('assignment_service_type_id');
    assignmentServiceSelect.addEventListener('change', function() {
        const serviceTypeId = this.value;
        const templatePricesAlert = document.getElementById('templatePricesAlert');
        
        if (!serviceTypeId) {
            templatePricesAlert.style.display = 'none';
            for (let i = 1; i <= 5; i++) {
                document.getElementById('display_price_' + i).textContent = '-';
            }
            return;
        }

        // Show loading state
        templatePricesAlert.style.display = 'block';
        for (let i = 1; i <= 5; i++) {
            document.getElementById('display_price_' + i).textContent = '...';
        }

        // Fetch template prices
        fetch('{{ route("service-price-list.get-templates") }}?service_type_id=' + serviceTypeId)
            .then(response => response.json())
            .then(data => {
                if (data.template) {
                    document.getElementById('display_price_1').textContent = parseFloat(data.template.price_1).toFixed(2);
                    document.getElementById('display_price_2').textContent = parseFloat(data.template.price_2).toFixed(2);
                    document.getElementById('display_price_3').textContent = parseFloat(data.template.price_3).toFixed(2);
                    document.getElementById('display_price_4').textContent = parseFloat(data.template.price_4).toFixed(2);
                    document.getElementById('display_price_5').textContent = parseFloat(data.template.price_5).toFixed(2);
                    templatePricesAlert.style.display = 'block';
                } else {
                    alert('{{ __("No price template found for this service type. Please create a template first.") }}');
                    this.value = '';
                    templatePricesAlert.style.display = 'none';
                    for (let i = 1; i <= 5; i++) {
                        document.getElementById('display_price_' + i).textContent = '-';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                templatePricesAlert.style.display = 'none';
            });
    });

    // Assignment Form Handlers
    document.querySelectorAll('.edit-assignment').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('assignment_service_type_id').value = this.dataset.service;
            document.getElementById('assignment_owner_id').value = this.dataset.owner;
            document.getElementById('assignment_assigned_price_list').value = this.dataset.assigned;
            document.getElementById('assignment_edit_id').value = this.dataset.id;
            
            // Update form title
            document.getElementById('assignmentFormTitle').textContent = '{{ __("Edit Assignment") }}';
            
            // Trigger change event to load template prices
            document.getElementById('assignment_service_type_id').dispatchEvent(new Event('change'));
            
            // Change form action to update
            const form = document.getElementById('assignmentForm');
            form.setAttribute('action', '{{ route("service-price-list.update", ":id") }}'.replace(':id', this.dataset.id));
            // Add method override
            if (!form.querySelector('input[name="_method"]')) {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
            }
            
            // Scroll to form with smooth animation
            document.getElementById('assignmentFormCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('assignmentFormCard').style.animation = 'pulse 0.5s';
        });
    });

    // Reset Assignment Form
    document.getElementById('resetAssignmentForm').addEventListener('click', function() {
        document.getElementById('assignmentForm').reset();
        document.getElementById('assignment_edit_id').value = '';
        document.getElementById('assignmentFormTitle').textContent = '{{ __("Assign to Owner") }}';
        const form = document.getElementById('assignmentForm');
        form.setAttribute('action', '{{ route("service-price-list.store") }}');
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) {
            methodInput.remove();
        }
        // Clear prices display
        document.getElementById('templatePricesAlert').style.display = 'none';
        for (let i = 1; i <= 5; i++) {
            document.getElementById('display_price_' + i).textContent = '-';
        }
    });
});
</script>
@endsection
