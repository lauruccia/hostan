@php
    $user = \Auth::user();
    $tenant = $user->tenants;
@endphp
{{ Form::model($maintenanceRequest, ['route' => ['maintenance-request.update', $maintenanceRequest->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        @if ($user->type == 'tenant')
            {{ Form::hidden('property_id', !empty($tenant) ? $tenant->property : null, ['class' => 'form-control']) }}
            {{ Form::hidden('unit_id', !empty($tenant) ? $tenant->unit : null, ['class' => 'form-control']) }}
        @else
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                {{ Form::select('property_id', $property, null, ['class' => 'form-control modern-select', 'id' => 'property_id']) }}
            </div>
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                <div class="unit_div">
                    <select class="form-control modern-select unit" id="unit_id" name="unit_id">
                        <option value="">{{ __('Select Unit') }}</option>
                    </select>
                </div>
            </div>
        @endif

{{-- 
        <div class="form-group  col-md-6 col-lg-6">
            {{ Form::label('request_date', __('Request Date'), ['class' => 'form-label']) }}
            {{ Form::date('request_date', null, ['class' => 'form-control']) }}
        </div> --}}
        {{-- Arrival Type --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('arrival_type', __('Arrival Type'), ['class'=>'form-label']) }}
            {{ Form::select('arrival_type', ['with_arrival' => __('With arrival'), 'without_arrival' => __('Without')], $maintenanceRequest->arrival_type, ['class'=>'form-control modern-select', 'id' => 'arrival_type']) }}
        </div>

        {{-- Arrival Date/Time --}}
        <div class="form-group col-md-6 col-lg-6" id="arrival_time_container">
            {{ Form::label('arrival_time', __('Service Date & Time'), ['class'=>'form-label']) }}

            {{ Form::datetimeLocal(
                'arrival_time', 
                old('arrival_time', optional($maintenanceRequest->arrival_time) 
                    ? \Carbon\Carbon::parse($maintenanceRequest->arrival_time)->format('Y-m-d\TH:i') 
                    : null
                ), 
                ['class' => 'form-control', 'id' => 'arrival_time']
            ) }}
        </div>
        @unless(Auth::user()->type == 'owner')
    <div class="form-group col-md-6 col-lg-6">
        {{ Form::label('maintainer_id', __('Maintainer'), ['class' => 'form-label']) }}
        {{ Form::select('maintainer_id', $maintainers, null, ['class' => 'form-control modern-select']) }}
    </div>
@endunless

        <div class="form-group col-md-6 col-lg-6">
    {{ Form::label('issue_type', __('Service Type'), ['class' => 'form-label']) }}
    {{ Form::select(
        'issue_type',
        $types,
        old('service_type', $maintenanceRequest->service_type ?? null),
        ['class' => 'form-control modern-select']
    ) }}
</div>

        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('people_count', __('Number of People'), ['class' => 'form-label']) }}
            {{ Form::select(
                'people_count',
                $peopleCount ?? [],
                old('people_count', $maintenanceRequest->people_count ?? null),
                ['class' => 'form-control modern-select'],
            ) }}
        </div>

        <div class="form-group  col-md-12 col-lg-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}

<style>
    /* Fix for double scrollbars in modals */
    .modal-body {
        max-height: 80vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Ensure the row doesn't cause horizontal overflow */
    .modal-body .row {
        margin-right: -5px;
        margin-left: -5px;
    }

    /* Modern Select Styling */
    .modern-select {
        display: block;
        width: 100%;
        padding: 10px 12px;
        font-size: 14px;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 8px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        appearance: none;
    }

    .modern-select:focus {
        border-color: #6c63ff;
        box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        outline: none;
    }

    .modern-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='none' stroke='%23333' d='M2 0L0 2h4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 8px 10px;
    }

    /* Fix for invisible text in dropdown options */
    .modern-select option {
        color: #333 !important;
        background-color: #fff !important;
    }
</style>

<script>
    $('#property_id').on('change', function() {
        "use strict";
        var property_id = $(this).val();
        var url = '{{ route('property.unit', ':id') }}';
        url = url.replace(':id', property_id);
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('.unit').empty();
                $('.unit').append('<option value="">{{ __("Select Unit") }}</option>');
                $.each(data, function(key, value) {
                    $('.unit').append('<option value="' + key + '">' + value + '</option>');
                });
                
                // Re-select unit if needed (logic for editing)
                var currentUnit = "{{ $maintenanceRequest->unit_id }}";
                if(currentUnit) {
                    $('.unit').val(currentUnit);
                }
            }
        });
    });

    $('#property_id').trigger('change');

    // toggleArrivalTime(); // Removed to keep date field visible
</script>
