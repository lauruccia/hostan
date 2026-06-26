@if ($maintenanceRequest->status == 'pending')
    {{-- Show original update form modal --}}
    {{ Form::model($maintenanceRequest, ['route' => ['maintenance-request.action', $maintenanceRequest->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('arrival_time', __('Service Date & Time'), ['class' => 'form-label']) }}
                {{ Form::datetimeLocal('arrival_time', !empty($maintenanceRequest->arrival_time) ? \Carbon\Carbon::parse($maintenanceRequest->arrival_time)->format('Y-m-d\TH:i') : null, ['class' => 'form-control']) }}
            </div>
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                {{ Form::select('status', $status, $maintenanceRequest->status, ['class' => 'form-control hidesearch', 'id' => 'statusSelect']) }}
            </div>
          
            <div class="form-group col-md-12 col-lg-12">
                {{ Form::label('invoice', __('Attachment'), ['class' => 'form-label']) }}
                {{ Form::file('invoice', ['class' => 'form-control']) }}
            </div>
            
            @if(auth()->user()->type == 'maintainer')
            <div class="form-group col-md-12 col-lg-12" id="completionImagesSection" style="display: none;">
                {{ Form::label('completion_images', __('Facility Photos (After Service)'), ['class' => 'form-label']) }}
                {{ Form::file('completion_images[]', ['class' => 'form-control', 'multiple', 'accept' => 'image/*']) }}
                <small class="text-muted">{{ __('You can select multiple images. Only shown when status is "Completed".') }}</small>
            </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
    </div>
    {{ Form::close() }}
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('statusSelect');
            const completionImagesSection = document.getElementById('completionImagesSection');
            
            if (statusSelect && completionImagesSection) {
                // Show/hide completion images section based on status
                function toggleCompletionImages() {
                    if (statusSelect.value === 'completed') {
                        completionImagesSection.style.display = 'block';
                    } else {
                        completionImagesSection.style.display = 'none';
                    }
                }
                
                // Initial check
                toggleCompletionImages();
                
                // Listen for changes
                statusSelect.addEventListener('change', toggleCompletionImages);
            }
        });
    </script>
@else
    <div class="modal-body">
        @if (!empty($maintenanceRequest->properties) && $maintenanceRequest->properties->exists)
            <h5 class="text-primary mb-3">{{ __('Property Details') }}</h5>
            <p><strong>{{ __('Property Name') }}:</strong> {{ $maintenanceRequest->properties->name }}</p>
            <p><strong>{{ __('Unit') }}:</strong> {{ $maintenanceRequest->units->name ?? '-' }}</p>
            <p><strong>{{ __('Issue') }}:</strong> {{ $maintenanceRequest->types->title ?? '-' }}</p>
            <p><strong>{{ __('Maintainer') }}:</strong> {{ $maintenanceRequest->maintainers->name ?? '-' }}</p>
            <p><strong>{{ __('Request Date') }}:</strong> {{ dateFormat($maintenanceRequest->request_date) }}</p>
            @if (!empty($maintenanceRequest->issue_attachment))
                <p><strong>{{ __('Attachment') }}:</strong>
                    <a href="{{ asset(Storage::url('upload/issue_attachment') . '/' . $maintenanceRequest->issue_attachment) }}"
                        target="_blank">
                        {{ __('View Attachment') }}
                    </a>
                </p>
            @endif

            <hr>
            <h5 class="text-primary mb-3">{{ __('Service Actions') }}</h5>
            <div class="d-flex gap-2 align-items-center">
                @if ($maintenanceRequest->status == 'in_progress' && !$maintenanceRequest->started_at)
                    <form action="{{ route('operator.start-timer', $maintenanceRequest->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">{{ __('Start') }}</button>
                    </form>
                @endif
                @if ($maintenanceRequest->status == 'in_progress' && $maintenanceRequest->started_at && !$maintenanceRequest->ended_at)
                    <form action="{{ route('operator.stop-timer', $maintenanceRequest->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">{{ __('End') }}</button>
                    </form>
                @endif
                @if ($maintenanceRequest->ended_at)
                    <span class="badge bg-primary fs-6 p-20 rounded d-flex align-items-center">
                        {{ __('Service Completed on') }}
                        {{ date('d M Y H:i', strtotime($maintenanceRequest->ended_at)) }}
                    </span>
                @endif

            </div>
        @else
            <div class="alert alert-warning">
                {{ __('No property information available.') }}
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>


@endif
