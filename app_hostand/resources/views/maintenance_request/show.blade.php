<div class="modal-body p-0">
    @php use Carbon\Carbon; @endphp

    {{-- Service overview card (theme-aligned header) --}}
    <div class="card border-0 shadow-sm rounded-0 rounded-top">
        <div class="card-header bg-light border-0 py-3">
            <h6 class="mb-0 text-primary">
                <i class="ti ti-clipboard-list me-2"></i>{{ __('Service Details') }}
            </h6>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Property') }}</span><br>
                        <strong>{{ !empty($maintenanceRequest->properties) ? $maintenanceRequest->properties->name : '-' }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Unit') }}</span><br>
                        <strong>{{ !empty($maintenanceRequest->units) ? $maintenanceRequest->units->name : '-' }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Service Type') }}</span><br>
                        <strong>{{ !empty($maintenanceRequest->types) ? $maintenanceRequest->types->title : '-' }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Maintainer') }}</span><br>
                        <strong>{{ !empty($maintenanceRequest->maintainers) ? $maintenanceRequest->maintainers->name : __('Unassigned') }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Rider') }}</span><br>
                        <strong>{{ !empty($maintenanceRequest->rider) ? $maintenanceRequest->rider->name : '-' }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Arrival Type') }}</span><br>
                        <strong>{{ $maintenanceRequest->arrival_type === 'without_arrival' ? __('Without') : __('With arrival') }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    @php
                        $arrivalTime = !empty($maintenanceRequest->arrival_time)
                            ? Carbon::parse($maintenanceRequest->arrival_time)->format('d/m/Y h:i A')
                            : '-';
                    @endphp
                    <p class="mb-0"><span class="text-muted small">{{ __('Service Date/Time') }}</span><br>
                        <strong>{{ $arrivalTime }}</strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Number of People') }}</span><br>
                        <strong>{{ !empty($maintenanceRequest->people_count) ? $maintenanceRequest->people_count : '-' }}</strong>
                    </p>
                </div>
                @if(auth()->user()->type != 'maintainer')
                <div class="col-md-6">
                    @php
                        $requestDate = !empty($maintenanceRequest->request_date)
                            ? Carbon::parse($maintenanceRequest->request_date)->format('d/m/Y h:i A')
                            : '-';
                    @endphp
                    <p class="mb-0"><span class="text-muted small">{{ __('Request Date') }}</span><br>
                        <strong>{{ $requestDate }}</strong>
                    </p>
                </div>
                @endif
                <div class="col-md-6">
                    <p class="mb-0"><span class="text-muted small">{{ __('Status') }}</span><br>
                        @if ($maintenanceRequest->status == 'pending')
                        <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @elseif($maintenanceRequest->status == 'in_progress')
                        <span class="badge bg-info">{{ __('In Progress') }}</span>
                        @else
                        <span class="badge bg-success">{{ __('Completed') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="px-3 pb-3">
        <div class="row">

            {{-- Service notes (added by admin when assigning – visible to operators) --}}
            @if (!empty(trim($maintenanceRequest->admin_notes ?? '')))
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm border-primary border-2">
                    <div class="card-header bg-primary bg-opacity-10 border-0 py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-message-circle me-2"></i>{{ __('Admin service note') }}</h6>
                    </div>
                    <div class="card-body py-3">
                        <p class="mb-0">{{ $maintenanceRequest->admin_notes }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Address & Access Information (from properties table only) --}}
            @php
                $prop = $maintenanceRequest->properties;
                $hasAddress = $prop && (trim($prop->address ?? '') !== '' || trim($prop->city ?? '') !== '' || trim($prop->state ?? '') !== '' || trim($prop->zip_code ?? '') !== '' || trim($prop->country ?? '') !== '');
                $hasAccess = $prop && (trim($prop->piano ?? '') !== '' || trim($prop->staircase ?? '') !== '' || trim($prop->sign_detail ?? '') !== '' || trim($prop->opening_type ?? '') !== '' || trim($prop->street_code ?? '') !== '' || trim($prop->door_code ?? '') !== '' || trim($prop->key_description ?? '') !== '' || trim($prop->access_other ?? '') !== '');
            @endphp
            @if ($prop && ($hasAddress || $hasAccess))
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0 py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-map-pin me-2"></i>{{ __('Address & Access Information') }}</h6>
                    </div>
                    <div class="card-body py-3">
                        @if ($hasAddress)
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">{{ __('Property Address') }}</span>
                            <p class="mb-0">
                                @if (!empty(trim($prop->address ?? ''))){{ $prop->address }}<br>@endif
                                @if (!empty(trim($prop->city ?? ''))){{ $prop->city }}@endif
                                @if (!empty(trim($prop->state ?? ''))){{ !empty(trim($prop->city ?? '')) ? ', ' : '' }}{{ $prop->state }}@endif
                                @if (!empty(trim($prop->zip_code ?? ''))){{ (!empty(trim($prop->city ?? '')) || !empty(trim($prop->state ?? ''))) ? ' ' : '' }}{{ $prop->zip_code }}@endif
                                @if (!empty(trim($prop->country ?? '')))<br>{{ $prop->country }}@endif
                            </p>
                        </div>
                        @endif
                        @if ($hasAccess)
                        <div class="row">
                            @if (!empty(trim($prop->piano ?? '')))
                            <div class="col-md-6 col-12 mb-2">
                                <b>{{ __('Piano') }}:</b> {{ $prop->piano }}
                            </div>
                            @endif
                            @if (!empty(trim($prop->staircase ?? '')))
                            <div class="col-md-6 col-12 mb-2">
                                <b>{{ __('Staircase') }}:</b> {{ $prop->staircase }}
                            </div>
                            @endif
                            @if (!empty(trim($prop->sign_detail ?? '')))
                            <div class="col-md-6 col-12 mb-2">
                                <b>{{ __('Name on the Doorbell') }}:</b> {{ $prop->sign_detail }}
                            </div>
                            @endif
                            @if (!empty(trim($prop->opening_type ?? '')))
                            <div class="col-md-6 col-12 mb-2">
                                <b>{{ __('Opening Type') }}:</b> {{ ucfirst($prop->opening_type) }}
                            </div>
                            @endif
                            @if (!empty(trim($prop->street_code ?? '')))
                            <div class="col-md-6 col-12 mb-2">
                                <b>{{ __('Street Code') }}:</b> {{ $prop->street_code }}
                            </div>
                            @endif
                            @if (!empty(trim($prop->door_code ?? '')))
                            <div class="col-md-6 col-12 mb-2">
                                <b>{{ __('Door Code') }}:</b> {{ $prop->door_code }}
                            </div>
                            @endif
                            @if (!empty(trim($prop->key_description ?? '')))
                            <div class="col-md-6 col-12 mb-2">
                                <b>{{ __('Key Description') }}:</b> {{ $prop->key_description }}
                            </div>
                            @endif
                            @if (!empty(trim($prop->access_other ?? '')))
                            <div class="col-12 mb-2">
                                <b>{{ __('Internal Access Description') }}:</b>
                                <p class="mb-0 mt-1 small">{{ $prop->access_other }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Unit details (only the unit included in this maintenance request) --}}
            @php
                $requestUnit = $maintenanceRequest->units;
                $unitFieldLabels = [
                    'name' => __('Name'),
                    'bedroom' => __('Bedroom'),
                    'bedroom_type' => __('Bedroom Type'),
                    'double_beds' => __('Double Beds'),
                    'single_beds' => __('Single Beds'),
                    'sofa_beds' => __('Sofa Beds'),
                    'baths' => __('Baths'),
                    'kitchen' => __('Kitchen'),
                    'description' => __('Description'),
                    'notes' => __('Notes'),
                    'piano' => __('Piano'),
                    'staircase' => __('Staircase'),
                    'sign_detail' => __('Sign Detail'),
                    'opening_type' => __('Opening Type'),
                    'street_code' => __('Street Code'),
                    'door_code' => __('Door Code'),
                    'key_description' => __('Key Description'),
                    'access_other' => __('Other Access Information'),
                    'access_description' => __('Access Description'),
                    'rent' => __('Rent'),
                    'deposit_amount' => __('Deposit Amount'),
                    'deposit_type' => __('Deposit Type'),
                    'rent_type' => __('Rent Type'),
                    'rent_duration' => __('Rent Duration'),
                    'late_fee_type' => __('Late Fee Type'),
                    'late_fee_amount' => __('Late Fee Amount'),
                    'incident_receipt_amount' => __('Incident Receipt Amount'),
                    'start_date' => __('Start Date'),
                    'end_date' => __('End Date'),
                    'payment_due_date' => __('Payment Due Date'),
                ];
            @endphp
            @if ($requestUnit)
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0 py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-building me-2"></i>{{ __('Unit details') }}</h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="row small">
                                @foreach ($unitFieldLabels as $field => $label)
                                    @php
                                        $raw = $requestUnit->$field ?? null;
                                        if ($raw === null || (string)$raw === '') continue;
                                        $val = $raw;
                                        if (in_array($field, ['start_date', 'end_date', 'payment_due_date'])) {
                                            try { $val = \Carbon\Carbon::parse($raw)->format('d/m/Y'); } catch (\Exception $e) { $val = $raw; }
                                        }
                                        if ($field === 'opening_type') $val = ucfirst($val);
                                    @endphp
                                    <div class="col-md-6 col-12 mb-2">
                                        <b>{{ $label }}:</b>
                                        @if (in_array($field, ['access_other', 'access_description', 'description', 'notes']) && strlen((string)$val) > 80)
                                        <span title="{{ $val }}">{{ Str::limit($val, 80) }}</span>
                                        @else
                                        {{ $val }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Property images (thumbnail + all images from property_images for this property) --}}
            @php
                $propForImages = $maintenanceRequest->properties;
                $hasThumbnail = $propForImages && $propForImages->thumbnail && !empty(trim($propForImages->thumbnail->image ?? ''));
                $extraImages = $propForImages ? $propForImages->propertyImages : collect();
                $hasPropertyImages = $hasThumbnail || $extraImages->isNotEmpty();
            @endphp
            @if (!empty($propForImages) && $hasPropertyImages)
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0 py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-photo me-2"></i>{{ __('Unit Photos') }}</h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex flex-wrap gap-2">
                            @if ($hasThumbnail)
                            @php
                                $thumbImage = trim($propForImages->thumbnail->image);
                                $thumbUrl = asset('uploads/property/' . $thumbImage);
                            @endphp
                            <a href="{{ $thumbUrl }}" class="glightbox" data-gallery="service-gallery">
                                <img src="{{ $thumbUrl }}" alt="{{ __('Property photo') }}"
                                    class="img-thumbnail rounded" style="width:120px; height:80px; object-fit:cover;">
                            </a>
                            @endif
                            @foreach ($extraImages as $image)
                            @if (!empty(trim($image->image ?? '')))
                            @php
                                $imgName = trim($image->image);
                                $imgUrl = asset('uploads/property/' . $imgName);
                            @endphp
                            <a href="{{ $imgUrl }}" class="glightbox" data-gallery="service-gallery">
                                <img src="{{ $imgUrl }}" alt="{{ __('Property photo') }}"
                                    class="img-thumbnail rounded" style="width:120px; height:80px; object-fit:cover;">
                            </a>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Completion Images (Facility Photos) --}}
            @php
            $completionImages = [];
            if (!empty($maintenanceRequest->completion_images)) {
                $completionImages = json_decode($maintenanceRequest->completion_images, true);
                if (!is_array($completionImages)) {
                    $completionImages = [];
                }
            }
            @endphp
            @if (!empty($completionImages) && count($completionImages) > 0)
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0 py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-camera me-2"></i>{{ __('Facility Photos (After Service)') }}</h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="d-flex flex-wrap gap-2">
                    @foreach ($completionImages as $imagePath)
                        @if (!empty($imagePath))
                            @php
                                // Database stores: "uploads/facility_images/filename.jfif"
                                $imagePath = trim($imagePath);
                                $imagePath = ltrim($imagePath, '/');
                                
                                if (strpos($imagePath, 'uploads/facility_images/') === 0) {
                                    $imageUrl = asset($imagePath);
                                } elseif (strpos($imagePath, 'upload/facility_images/') === 0) {
                                    $imagePath = str_replace('upload/facility_images/', 'uploads/facility_images/', $imagePath);
                                    $imageUrl = asset($imagePath);
                                } else {
                                    $filename = basename($imagePath);
                                    $imageUrl = asset('uploads/facility_images/' . $filename);
                                }
                            @endphp
                            <a href="{{ $imageUrl }}" class="glightbox" data-gallery="service-gallery">
                                <img src="{{ $imageUrl }}" alt="Facility Photo"
                                    class="img-thumbnail rounded" style="width:120px; height:80px; object-fit:cover; cursor:pointer;">
                            </a>
                        @endif
                    @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes / Ticket --}}
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0 py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-notes me-2"></i>{{ __('Notes / Ticket') }}</h6>
                    </div>
                    <div class="card-body py-3">
                        <p class="mb-0 text-muted">{{ $maintenanceRequest->notes ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Ticket Attachments --}}
            {{-- @if (!empty($maintenanceRequest->issue_attachment))
                <div class="col-12 mt-2">
                    <b>{{__('Ticket Attachments')}} :</b>
            <a href="{{ asset(Storage::url('upload/issue_attachment')).'/'.$maintenanceRequest->issue_attachment }}"
                target="_blank">
                <i class="fa fa-image"></i> View
            </a>
        </div>
        @endif --}}

        {{-- Clockin / Clockout --}}
        @if (auth()->user()->type == 'operator')
        <div class="col-12 mt-4 text-center">
            @if ($maintenanceRequest->status == 'pending')
            <form action="{{ route('maintenance-request.clockin', $maintenanceRequest->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success"><i class="ti ti-player-play me-1"></i>{{ __('Clock In (Start)') }}</button>
            </form>
            @elseif($maintenanceRequest->status == 'in_progress')
            <form action="{{ route('maintenance-request.clockout', $maintenanceRequest->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger"><i class="ti ti-player-stop me-1"></i>{{ __('Clock Out (End)') }}</button>
            </form>
            @endif
        </div>
        @endif

        {{-- Super Admin: Work Session Info --}}
        @if (auth()->user()->type == 'super admin')
        <div class="col-12 mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white border-0 py-2">
                    <h6 class="mb-0"><i class="ti ti-clock me-2"></i>{{ __('Work Session Details') }}</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        {{-- Started At --}}
                        <div class="col-md-4 col-12 mb-3">
                            <p class="mb-1 text-muted"><b>{{ __('Started At') }}:</b></p>
                            <p class="fw-semibold">
                                {{ !empty($maintenanceRequest->started_at) ? $maintenanceRequest->started_at : '-' }}
                            </p>
                        </div>

                        {{-- Ended At --}}
                        <div class="col-md-4 col-12 mb-3">
                            <p class="mb-1 text-muted"><b>{{ __('Ended At') }}:</b></p>
                            <p class="fw-semibold">
                                {{ !empty($maintenanceRequest->ended_at) ? $maintenanceRequest->ended_at : '-' }}
                            </p>
                        </div>

                        {{-- Hours Worked --}}
                        <div class="col-md-4 col-12 mb-3">
                            <p class="mb-1 text-muted"><b>{{ __('Hours Worked') }}:</b></p>
                            <p class="fw-semibold">
                                @if(!empty($maintenanceRequest->hours_worked))
                                @php
                                $totalSeconds = $maintenanceRequest->hours_worked * 3600; // convert hours to seconds
                                $hours = floor($totalSeconds / 3600);
                                $minutes = floor(($totalSeconds % 3600) / 60);
                                $seconds = round($totalSeconds % 60);
                                @endphp
                                {{ $hours }} {{ __('hours') }} {{ $minutes }} {{ __('minutes') }} {{ $seconds }} {{ __('seconds') }}
                                @else
                                -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        </div>
    </div>
</div>
