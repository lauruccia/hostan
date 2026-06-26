@extends('layouts.app')
@section('page-title')
    {{ __('Daily Plan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">{{ __('Operator Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Daily Plan') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Daily Plan') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-calendar"></i> {{ __('Today\'s assigned services for') }} {{ $operator->first_name }} {{ $operator->last_name }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                                {{-- <a href="{{ route('operator.dashboard') }}" class="btn btn-light me-2">
                                    <i class="ti ti-arrow-left"></i> {{ __('Back to Dashboard')  }} --}}
                            </a>
                            <a href="{{ route('operator.daily-plan') }}" class="btn btn-light">
                                <i class="ti ti-calendar"></i> {{ __('Daily Plan') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-warning">
                        <i class="ti ti-clock"></i> {{ __('Pending Services') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-warning mb-0">{{ $pendingServices->count() }}</h2>
                    <p class="text-muted mb-0">{{ __('Awaiting to start') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-tools"></i> {{ __('In Progress') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-info mb-0">{{ $inProgressServices->count() }}</h2>
                    <p class="text-muted mb-0">{{ __('Currently working') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-success">
                        <i class="ti ti-check-circle"></i> {{ __('Completed') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-0">{{ $completedServices->count() }}</h2>
                    <p class="text-muted mb-0">{{ __('Finished today') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Services by Status -->
    <div class="row">
        <!-- Pending Services -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-warning">
                        <i class="ti ti-clock"></i> {{ __('Pending Services') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($pendingServices->count() > 0)
                        @foreach($pendingServices as $service)
                            <div class="card mb-3 border-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $service->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $service->units->name ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-warning">{{ __('Pending') }}</span>
                                    </div>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Service:') }}</strong> {{ $service->types->title ?? 'N/A' }}
                                    </p>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Time:') }}</strong> 
                                        @php
                                            $timeDisplay = '-';
                                            if (!empty($service->arrival_time)) {
                                                // Parse arrival_time from UTC and convert to user's timezone for display
                                                $settings = settings();
                                                $timezone = !empty($settings['timezone']) && $settings['timezone'] !== '' ? $settings['timezone'] : 'UTC';
                                                $timeDisplay = \Carbon\Carbon::parse($service->arrival_time, 'UTC')
                                                    ->setTimezone($timezone)
                                                    ->format('h:i A');
                                            }
                                        @endphp
                                        {{ $timeDisplay }}
                                    </p>
                                    
                                    @if($service->description)
                                        <p class="mb-2 small text-muted">{{ $service->description }}</p>
                                    @endif
                                    
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('operator.update-service-status', $service->id) }}" class="flex-grow-1">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                                <i class="ti ti-play"></i> {{ __('Start Work') }}
                                            </button>
                                        </form>
                                        
                                        <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                           data-size="lg" data-title="{{ __('View Details') }}" 
                                           data-url="{{ route('maintenance-request.show', $service->id) }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                            <p class="text-muted mt-2">{{ __('No pending services!') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- In Progress Services -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-tools"></i> {{ __('In Progress') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($inProgressServices->count() > 0)
                        @foreach($inProgressServices as $service)
                            <div class="card mb-3 border-info">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $service->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $service->units->name ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-info">{{ __('In Progress') }}</span>
                                    </div>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Service:') }}</strong> {{ $service->types->title ?? 'N/A' }}
                                    </p>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Started:') }}</strong> 
                                        @php
                                            $timeDisplay = '-';
                                            if (!empty($service->arrival_time)) {
                                                // Parse arrival_time from UTC and convert to user's timezone for display
                                                $settings = settings();
                                                $timezone = !empty($settings['timezone']) && $settings['timezone'] !== '' ? $settings['timezone'] : 'UTC';
                                                $timeDisplay = \Carbon\Carbon::parse($service->arrival_time, 'UTC')
                                                    ->setTimezone($timezone)
                                                    ->format('h:i A');
                                            }
                                        @endphp
                                        {{ $timeDisplay }}
                                    </p>
                                    
                                    @if($service->hours_worked)
                                        <p class="mb-2">
                                            <strong>{{ __('Hours Worked:') }}</strong> {{ number_format($service->hours_worked, 1) }}h
                                        </p>
                                    @endif
                                    
                                    <!-- Image Upload and Actions Section -->
                                    <div class="border-top pt-3 mt-3">
                                        <form method="POST" action="{{ route('operator.update-service-status', $service->id) }}" enctype="multipart/form-data" id="completeForm{{ $service->id }}">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            
                                            <!-- Image Upload Section -->
                                            <div class="mb-3">
                                                <label for="completion_images{{ $service->id }}" class="form-label fw-semibold mb-2 d-flex align-items-center">
                                                    <i class="ti ti-photo me-2 text-primary"></i> 
                                                    <span>{{ __('Upload Facility Photos') }}</span>
                                                    <span class="badge bg-secondary ms-2">{{ __('Optional') }}</span>
                                                </label>
                                                <input type="file" 
                                                       class="form-control form-control-sm" 
                                                       id="completion_images{{ $service->id }}" 
                                                       name="completion_images[]" 
                                                       accept="image/*" 
                                                       multiple
                                                       onchange="previewImages(event, 'preview{{ $service->id }}')">
                                                <small class="text-muted d-block mt-1">
                                                    <i class="ti ti-info-circle"></i> {{ __('You can select multiple images') }}
                                                </small>
                                                
                                                <!-- Image Preview Container -->
                                                <div id="preview{{ $service->id }}" class="d-flex flex-wrap gap-2 mt-3 align-items-center justify-content-center" style="min-height: 70px; padding: 10px; background: #f8f9fa; border-radius: 6px; border: 1px dashed #dee2e6; display: none;">
                                                    <small class="text-muted text-center w-100" id="previewPlaceholder{{ $service->id }}">
                                                        <i class="ti ti-photo"></i> {{ __('Selected images will appear here') }}
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-success btn-sm flex-grow-1">
                                                    <i class="ti ti-check me-1"></i> {{ __('Mark Complete') }}
                                            </button>
                                        
                                        <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                           data-size="lg" data-title="{{ __('View Details') }}" 
                                           data-url="{{ route('maintenance-request.show', $service->id) }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Timer Section -->
                                    <div class="mt-3">
                                        @if($service->timer_started_at)
                                            <div class="alert alert-info py-2">
                                                <small>
                                                    <i class="ti ti-clock"></i> 
                                                    {{ __('Timer running since') }} {{ \Carbon\Carbon::parse($service->timer_started_at)->format('H:i') }}
                                                </small>
                                            </div>
                                            <form method="POST" action="{{ route('operator.stop-timer', $service->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                                    <i class="ti ti-square"></i> {{ __('Stop Timer') }}
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('operator.start-timer', $service->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info btn-sm">
                                                    <i class="ti ti-play"></i> {{ __('Start Timer') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-tools" style="font-size: 2rem; color: #17a2b8;"></i>
                            <p class="text-muted mt-2">{{ __('No services in progress.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Completed Services -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-success">
                        <i class="ti ti-check-circle"></i> {{ __('Completed Today') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($completedServices->count() > 0)
                        @foreach($completedServices as $service)
                            <div class="card mb-3 border-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $service->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $service->units->name ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                    </div>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Service:') }}</strong> {{ $service->types->title ?? 'N/A' }}
                                    </p>
                                    
                                    @if($service->hours_worked)
                                        <p class="mb-2">
                                            <strong>{{ __('Hours Worked:') }}</strong> 
                                            @if($service->hours_worked < 1)
                                                @php
                                                    $minutes = round($service->hours_worked * 60);
                                                @endphp
                                                {{ $minutes }} {{ __('min') }}
                                            @else
                                                {{ number_format($service->hours_worked, 1) }}h
                                            @endif
                                        </p>
                                    @endif
                                    
                                    @if($service->completed_at)
                                        <p class="mb-2">
                                            <strong>{{ __('Completed at:') }}</strong> 
                                            {{ \Carbon\Carbon::parse($service->completed_at)->format('H:i') }}
                                        </p>
                                    @endif
                                    
                                    <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                       data-size="lg" data-title="{{ __('View Details') }}" 
                                       data-url="{{ route('maintenance-request.show', $service->id) }}">
                                        <i class="ti ti-eye"></i> {{ __('View Details') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                            <p class="text-muted mt-2">{{ __('No completed services today.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-bolt"></i> {{ __('Quick Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('operator.weekly-plan') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-calendar-week"></i> {{ __('Weekly Plan') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('operator.reports') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-report"></i> {{ __('Reports') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('maintenance-request.index') }}" class="btn btn-outline-success w-100">
                                <i class="ti ti-list"></i> {{ __('All Services') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 

@push('script-page')
<script>
    function previewImages(event, previewId) {
        const preview = document.getElementById(previewId);
        if (!preview) return;
        
        const placeholder = document.getElementById(previewId.replace('preview', 'previewPlaceholder'));
        const files = event.target.files;
        
        if (!files || files.length === 0) {
            preview.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';
            return;
        }
        
        // Hide placeholder and show preview
        if (placeholder) placeholder.style.display = 'none';
        preview.style.display = 'flex';
        
        // Remove old preview images (except placeholder)
        const oldImages = preview.querySelectorAll('.img-preview-container');
        oldImages.forEach(img => img.remove());
        
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'img-preview-container';
                imgContainer.style.position = 'relative';
                imgContainer.style.display = 'inline-block';
                imgContainer.style.margin = '2px';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '70px';
                img.style.height = '70px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '6px';
                img.style.border = '2px solid #fff';
                img.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
                img.style.cursor = 'pointer';
                img.className = 'img-preview';
                img.title = files[i].name;
                
                // Add hover effect
                img.onmouseover = function() {
                    this.style.transform = 'scale(1.1)';
                    this.style.transition = 'transform 0.2s';
                };
                img.onmouseout = function() {
                    this.style.transform = 'scale(1)';
                };
                
                imgContainer.appendChild(img);
                preview.appendChild(imgContainer);
            };
            reader.readAsDataURL(files[i]);
        }
    }
</script>
@endpush 