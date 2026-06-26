@extends('layouts.app')
@section('page-title')
    {{ __('Invoice') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script>
        // Flag to prevent multiple simultaneous API calls
        var isLoadingServices = false;
        
        $('#property_id').on('change', function() {
            "use strict";
            var property_id = $(this).val();
            var url = '{{ route('property.unit', ':id') }}';
            url = url.replace(':id', property_id);
            $.ajax({
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    property_id: property_id,
                },
                contentType: false,
                processData: false,
                type: 'GET',
                success: function(data) {
                    $('.unit').empty();
                    var unit =
                        `<select class="form-control hidesearch unit" id="unit" name="unit_id"></select>`;
                    $('.unit_div').html(unit);

                    $.each(data, function(key, value) {
                        $('.unit').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $(".hidesearch").each(function() {
                        var basic_select = new Choices(this, {
                            searchEnabled: false,
                            removeItemButton: true,
                        });
                    });
                    
                    // Trigger auto-load services check after unit is loaded
                    setTimeout(function() {
                        checkAndLoadServices();
                    }, 500);
                },

            });
        });
        
        // Auto-load services when all required fields are filled
        function checkAndLoadServices() {
            var propertyId = $('#property_id').val();
            
            // Try multiple ways to get unit ID (Choices.js might change the structure)
            var unitId = null;
            var $unitSelect = $('#unit, select[name="unit_id"]');
            if ($unitSelect.length) {
                unitId = $unitSelect.val();
                // If Choices.js is used, try to get value differently
                if (typeof Choices !== 'undefined' && !unitId) {
                    try {
                        var choicesInstance = Choices.getInstance($unitSelect[0]);
                        if (choicesInstance) {
                            unitId = choicesInstance.getValue(true);
                        }
                    } catch(e) {
                        // Choices instance not found
                    }
                }
            }
            
            var invoiceMonth = $('#invoice_month').val();
            var endDate = $('#end_date').val();

            // Only load if all fields are filled and not already loading
            if (propertyId && unitId && invoiceMonth && endDate && !isLoadingServices) {
                loadServicesForInvoice(propertyId, unitId, invoiceMonth, endDate);
            }
        }
        
        function loadServicesForInvoice(propertyId, unitId, invoiceMonth, endDate) {
            // Prevent multiple simultaneous calls
            if (isLoadingServices) {
                return;
            }
            
            isLoadingServices = true;
            
            // Calculate start date from invoice month (first day of the month)
            var startDate = invoiceMonth + '-01';
            
            // Clear existing rows FIRST before making the API call to prevent duplicates
            clearAllRepeaterRows();
            
            // Wait a bit to ensure rows are cleared before making API call
            setTimeout(function() {
                $.ajax({
                url: '{{ route("service-price-list.get-services-for-invoice") }}',
                method: 'GET',
                data: {
                    property_id: propertyId,
                    unit_id: unitId,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    // Reset loading flag
                    isLoadingServices = false;
                    
                    if (response.services && response.services.length > 0) {
                        // Get the repeater instance from global variable
                        var $repeater = window.repeaterInstance;
                        
                        if (!$repeater) {
                            if (typeof toastr !== 'undefined') {
                                toastr.error('{{ __("Error: Repeater not initialized") }}');
                            }
                            return;
                        }
                        
                        // Wait a bit to ensure rows are cleared, then load new services
                        setTimeout(function() {
                            // Add services to repeater
                            response.services.forEach(function(service, index) {
                            if (index === 0) {
                                // Use first row - wait a bit to ensure it exists
                                setTimeout(function() {
                                    var $firstRow = $('.repeater tbody tr[data-repeater-item]:first');
                                    var $select = $firstRow.find('select[name*="[invoice_type]"]');
                                    var $amountInput = $firstRow.find('input[name*="[amount]"]');
                                    var $descriptionInput = $firstRow.find('textarea[name*="[description]"]');
                                    
                                    if ($select.length === 0) {
                                        $select = $('.repeater select[name*="[invoice_type]"]:first');
                                        $amountInput = $('.repeater input[name*="[amount]"]:first');
                                        $descriptionInput = $('.repeater textarea[name*="[description]"]:first');
                                    }
                                    
                                    if ($select.length === 0) {
                                        return;
                                    }
                                    
                                    var serviceTypeId = String(service.service_type_id);
                                    
                                    // First, set the native select value - this is critical
                                    $select.val(serviceTypeId);
                                
                                    // Then set it in Choices.js with proper timing
                                    if (typeof Choices !== 'undefined') {
                                        var selectElement = $select[0];
                                        
                                        // Function to properly set and display value in Choices.js
                                        var setChoicesValue = function(instance) {
                                            try {
                                                // First ensure native select has the value
                                                $select.val(serviceTypeId);
                                                
                                                // Then set in Choices.js
                                                instance.setChoiceByValue(serviceTypeId);
                                                
                                                // Force a refresh of the display
                                                setTimeout(function() {
                                                    // Check if value is actually set
                                                    var currentValue = instance.getValue(true);
                                                    if (currentValue !== serviceTypeId) {
                                                        // If not set, try again
                                                        $select.val(serviceTypeId);
                                                        instance.setChoiceByValue(serviceTypeId);
                                                    }
                                                }, 50);
                                            } catch(e) {
                                                // If setChoiceByValue fails, ensure native select has value
                                                $select.val(serviceTypeId);
                                            }
                                        };
                                        
                                        // Wait to ensure Choices is ready
                                        setTimeout(function() {
                                            try {
                                                // Check if Choices.getInstance exists
                                                var choicesInstance = null;
                                                if (typeof Choices.getInstance === 'function') {
                                                    choicesInstance = Choices.getInstance(selectElement);
                                                } else {
                                                    // Try alternative method - check if element has Choices data
                                                    if (selectElement && selectElement.choices) {
                                                        choicesInstance = selectElement.choices;
                                                    }
                                                }
                                                
                                                if (choicesInstance) {
                                                    // Instance exists - destroy and recreate with value pre-selected
                                                    if (typeof choicesInstance.destroy === 'function') {
                                                        choicesInstance.destroy();
                                                    }
                                                    // Set value in native select first
                                                    $select.val(serviceTypeId);
                                                    
                                                    // Create new instance - it will pick up the value from native select
                                                    var newInstance = new Choices(selectElement, {
                                                        searchEnabled: false,
                                                        removeItemButton: true,
                                                    });
                                                    
                                                    // Double-check value is set
                                                    setTimeout(function() {
                                                        var currentValue = newInstance.getValue(true);
                                                        if (currentValue !== serviceTypeId) {
                                                            newInstance.setChoiceByValue(serviceTypeId);
                                                        }
                                                    }, 100);
                                                } else {
                                                    // No instance - set value first, then create instance
                                                    $select.val(serviceTypeId);
                                                    
                                                    var newInstance = new Choices(selectElement, {
                                                        searchEnabled: false,
                                                        removeItemButton: true,
                                                    });
                                                    
                                                    // Verify value after initialization
                                                    setTimeout(function() {
                                                        var currentValue = newInstance.getValue(true);
                                                        if (currentValue !== serviceTypeId) {
                                                            newInstance.setChoiceByValue(serviceTypeId);
                                                        }
                                                    }, 100);
                                                }
                                            } catch(e) {
                                                // Fallback: use native select
                                                $select.val(serviceTypeId).trigger('change');
                                            }
                                        }, 200);
                                    } else {
                                        // Fallback: trigger change to ensure value is set
                                        $select.val(serviceTypeId).trigger('change');
                                    }
                                    
                                    // Set amount and description - only description from maintenance request notes
                                    setTimeout(function() {
                                        var amountValue = parseFloat(service.price).toFixed(2);
                                        $amountInput.val(amountValue);
                                        
                                        // Only use the description from maintenance request, NOT the service name
                                        // Service name should be in the dropdown, not description
                                        var descriptionText = service.description || '';
                                        $descriptionInput.val(descriptionText);
                                        $amountInput.trigger('change');
                                    }, 200);
                                    
                                    // Also trigger change on select after setting amount
                                    setTimeout(function() {
                                        $select.trigger('change');
                                    }, 250);
                                }, 100); // Wait 100ms for first row to be ready
                                
                                // Set amount and description - only description from maintenance request notes
                                setTimeout(function() {
                                    var amountValue = parseFloat(service.price).toFixed(2);
                                    $amountInput.val(amountValue);
                                    
                                    // Only use the description from maintenance request, NOT the service name
                                    // Service name should be in the dropdown, not description
                                    var descriptionText = service.description || '';
                                    $descriptionInput.val(descriptionText);
                                    $amountInput.trigger('change');
                                }, 200);
                                
                                // Also trigger change on select after setting amount
                                setTimeout(function() {
                                    $select.trigger('change');
                                }, 250);
                            } else {
                                // Add new rows by triggering the repeater create button
                                var $addButton = $('.repeater [data-repeater-create]');
                                
                                if ($addButton.length) {
                                    // Create a callback function that will be executed when the row is actually created
                                    var serviceData = service; // Store service data for the callback
                                    var serviceIndex = index;
                                    
                                    var rowCallback = function($newRow) {
                                        var $select = $newRow.find('select[name*="[invoice_type]"]');
                                        var $amountInput = $newRow.find('input[name*="[amount]"]');
                                        var $descriptionInput = $newRow.find('textarea[name*="[description]"]');
                                        
                                        if ($select.length && $amountInput.length && $descriptionInput.length) {
                                            // Set service type first - ensure dropdown shows the service name
                                            var serviceTypeId = String(serviceData.service_type_id);
                                            $select.val(serviceTypeId);
                                            
                                            // Reinitialize Choices.js for the select to properly display the selected value
                                            if (typeof Choices !== 'undefined') {
                                                var selectElement = $select[0];
                                                
                                                // Function to properly set and display value in Choices.js
                                                var setChoicesValue = function(instance) {
                                                    try {
                                                        // First ensure native select has the value
                                                        $select.val(serviceTypeId);
                                                        
                                                        // Then set in Choices.js
                                                        instance.setChoiceByValue(serviceTypeId);
                                                        
                                                        // Force a refresh of the display
                                                        setTimeout(function() {
                                                            // Check if value is actually set
                                                            var currentValue = instance.getValue(true);
                                                            if (currentValue !== serviceTypeId) {
                                                                // If not set, try again
                                                                $select.val(serviceTypeId);
                                                                instance.setChoiceByValue(serviceTypeId);
                                                            }
                                                        }, 50);
                                                    } catch(e) {
                                                        // If setChoiceByValue fails, ensure native select has value
                                                        $select.val(serviceTypeId);
                                                    }
                                                };
                                                
                                                // Wait to ensure Choices is ready
                                                setTimeout(function() {
                                                    try {
                                                        // Check if Choices.getInstance exists
                                                        var choicesInstance = null;
                                                        if (typeof Choices.getInstance === 'function') {
                                                            choicesInstance = Choices.getInstance(selectElement);
                                                        } else {
                                                            // Try alternative method - check if element has Choices data
                                                            if (selectElement && selectElement.choices) {
                                                                choicesInstance = selectElement.choices;
                                                            }
                                                        }
                                                        
                                                        if (choicesInstance) {
                                                            // Instance exists - destroy and recreate with value pre-selected
                                                            if (typeof choicesInstance.destroy === 'function') {
                                                                choicesInstance.destroy();
                                                            }
                                                            // Set value in native select first
                                                            $select.val(serviceTypeId);
                                                            
                                                            // Create new instance - it will pick up the value from native select
                                                            var newInstance = new Choices(selectElement, {
                                                                searchEnabled: false,
                                                                removeItemButton: true,
                                                            });
                                                            
                                                            // Double-check value is set
                                                            setTimeout(function() {
                                                                var currentValue = newInstance.getValue(true);
                                                                if (currentValue !== serviceTypeId) {
                                                                    newInstance.setChoiceByValue(serviceTypeId);
                                                                }
                                                            }, 100);
                                                        } else {
                                                            // No instance - set value first, then create instance
                                                            $select.val(serviceTypeId);
                                                            
                                                            var newInstance = new Choices(selectElement, {
                                                                searchEnabled: false,
                                                                removeItemButton: true,
                                                            });
                                                            
                                                            // Verify value after initialization
                                                            setTimeout(function() {
                                                                var currentValue = newInstance.getValue(true);
                                                                if (currentValue !== serviceTypeId) {
                                                                    newInstance.setChoiceByValue(serviceTypeId);
                                                                }
                                                            }, 100);
                                                        }
                                                    } catch(e) {
                                                        // Fallback: use native select
                                                        $select.val(serviceTypeId).trigger('change');
                                                    }
                                                }, 200);
                                            } else {
                                                // Fallback: trigger change to ensure value is set
                                                $select.val(serviceData.service_type_id).trigger('change');
                                            }
                                            
                                            // Set amount and description - only description from maintenance request notes
                                            var amountValue = parseFloat(serviceData.price).toFixed(2);
                                            $amountInput.val(amountValue);
                                            
                                            // Only use the description from maintenance request, NOT the service name
                                            // Service name should be in the dropdown, not description
                                            var descriptionText = serviceData.description || '';
                                            $descriptionInput.val(descriptionText);
                                            
                                            // Trigger change events
                                            $amountInput.trigger('change');
                                            setTimeout(function() {
                                                $select.trigger('change');
                                            }, 50);
                                        }
                                    };
                                    
                                    // Add callback to queue
                                    if (!window.repeaterCallbacks) {
                                        window.repeaterCallbacks = [];
                                    }
                                    window.repeaterCallbacks.push(rowCallback);
                                    
                                    // Trigger click on the add button
                                    $addButton.trigger('click');
                                }
                            }
                            });
                            
                            // Remove empty rows after all services are loaded
                            // Wait a bit longer to ensure all rows are fully populated
                            setTimeout(function() {
                                removeEmptyRows();
                            }, 1000);
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __("Services loaded successfully") }}: ' + response.services.length + ' {{ __("services found for the selected period") }}');
                            }
                        }, 200);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.info('{{ __("No services found for the selected period") }}');
                        }
                    }
                },
                error: function(xhr) {
                    // Reset loading flag on error
                    isLoadingServices = false;
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __("Error loading services") }}');
                    }
                }
                });
            }, 500); // Wait 500ms for rows to be cleared
        }
        
        // Function to clear all repeater rows (except first template row)
        function clearAllRepeaterRows() {
            var $allRows = $('.repeater tbody tr[data-repeater-item]');
            
            if ($allRows.length === 0) {
                return;
            }
            
            // Remove all rows except the first one (template row)
            var rowsToRemove = $allRows.not(':first');
            
            rowsToRemove.each(function(index) {
                var $row = $(this);
                var $deleteButton = $row.find('[data-repeater-delete]');
                
                // Use a small delay for each deletion to ensure proper removal
                setTimeout(function() {
                    if ($deleteButton.length) {
                        // Use repeater's delete functionality
                        $deleteButton.trigger('click');
                    } else {
                        // Fallback: remove directly with animation
                        $row.slideUp(200, function() {
                            $(this).remove();
                        });
                    }
                }, index * 50); // Stagger deletions
            });
            
            // Clear the first row's values too (reset template)
            var $firstRow = $('.repeater tbody tr[data-repeater-item]:first');
            if ($firstRow.length) {
                setTimeout(function() {
                    $firstRow.find('select[name*="[invoice_type]"]').val('').trigger('change');
                    $firstRow.find('input[name*="[amount]"]').val('');
                    $firstRow.find('textarea[name*="[description]"]').val('');
                }, 100);
            }
        }
        
        // Function to remove empty rows from repeater (only after data is loaded)
        function removeEmptyRows() {
            var $allRows = $('.repeater tbody tr[data-repeater-item]');
            
            if ($allRows.length === 0) {
                return;
            }
            
            $allRows.each(function(index) {
                var $row = $(this);
                var $select = $row.find('select[name*="[invoice_type]"]');
                var $amountInput = $row.find('input[name*="[amount]"]');
                var $descriptionInput = $row.find('textarea[name*="[description]"]');
                
                var hasType = $select.val() && $select.val() !== '' && $select.val() !== '0' && $select.val() !== null;
                var hasAmount = $amountInput.val() && $amountInput.val() !== '' && parseFloat($amountInput.val()) > 0;
                var hasDescription = $descriptionInput.val() && $descriptionInput.val().trim() !== '';
                
                // Check if row is completely empty (no type, no amount, no description)
                var isEmpty = !hasType && !hasAmount && !hasDescription;
                
                // Remove empty rows, but keep at least one row (the first one) as template
                // Only remove if there are multiple rows and this one is empty
                if (isEmpty && $allRows.length > 1) {
                    // Use repeater's delete functionality if available
                    var $deleteButton = $row.find('[data-repeater-delete]');
                    if ($deleteButton.length) {
                        $deleteButton.trigger('click');
                    } else {
                        // Fallback: remove directly
                        $row.slideUp(function() {
                            $(this).remove();
                        });
                    }
                }
            });
        }
        
        // Listen for changes on unit, invoice month, and end date
        $(document).on('change', '#unit, select[name="unit_id"], #invoice_month, #end_date', function() {
            checkAndLoadServices();
        });
        
        // Also listen for Choices.js changes on unit
        $(document).ready(function() {
            // Wait a bit for Choices.js to initialize
            setTimeout(function() {
                var $unitSelect = $('#unit, select[name="unit_id"]');
                if ($unitSelect.length && typeof Choices !== 'undefined') {
                    try {
                        var choicesInstance = Choices.getInstance($unitSelect[0]);
                        if (choicesInstance) {
                            $unitSelect[0].addEventListener('choice', function(event) {
                                setTimeout(function() {
                                    checkAndLoadServices();
                                }, 100);
                            });
                        }
                    } catch(e) {
                        // Could not attach Choices.js listener
                    }
                }
            }, 1000);
        });
        
        // Filter out empty rows before form submission
        $('#invoice_form').on('submit', function(e) {
            // Remove empty rows from the form data
            $('.repeater tbody tr[data-repeater-item]').each(function() {
                var $row = $(this);
                var $select = $row.find('select[name*="[invoice_type]"]');
                var $amountInput = $row.find('input[name*="[amount]"]');
                var $descriptionInput = $row.find('textarea[name*="[description]"]');
                
                var hasType = $select.val() && $select.val() !== '' && $select.val() !== '0' && $select.val() !== null;
                var hasAmount = $amountInput.val() && $amountInput.val() !== '' && parseFloat($amountInput.val()) > 0;
                var hasDescription = $descriptionInput.val() && $descriptionInput.val().trim() !== '';
                
                // If row is completely empty, remove it from DOM before submission
                if (!hasType && !hasAmount && !hasDescription) {
                    $row.remove();
                }
            });
        });
    </script>
    <script>
        var selector = "body";
        // Store repeater instance globally so it can be accessed from other functions
        window.repeaterInstance = null;
        
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({

                handle: '.sort-handler'
            });
            // Store callback queue for when rows are added programmatically
            window.repeaterCallbacks = [];
            
            var $repeater = $(selector + ' .repeater').repeater({

                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function() {
                    var $newRow = $(this);
                    
                    // Don't initialize Choices.js here - let the callback do it after setting values
                    // This ensures the value is set in native select before Choices.js initializes
                    
                    $newRow.slideDown();
                    
                    // Execute any pending callbacks for this row after a small delay
                    // The callback will set the value first, then initialize Choices.js
                    setTimeout(function() {
                        if (window.repeaterCallbacks.length > 0) {
                            var callback = window.repeaterCallbacks.shift();
                            if (callback && typeof callback === 'function') {
                                callback($newRow);
                            }
                        } else {
                            // If no callback, initialize Choices.js normally
                            $newRow.find(".hidesearch").each(function() {
                                if (typeof Choices !== 'undefined') {
                                    try {
                                        var existingInstance = Choices.getInstance(this);
                                        if (!existingInstance) {
                                            var basic_select = new Choices(this, {
                                                searchEnabled: false,
                                                removeItemButton: true,
                                            });
                                        }
                                    } catch(e) {
                                        var basic_select = new Choices(this, {
                                            searchEnabled: false,
                                            removeItemButton: true,
                                        });
                                    }
                                }
                            });
                        }
                    }, 50);
                },
                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();

                    }
                },
                ready: function(setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            
            // Store repeater instance globally
            window.repeaterInstance = $repeater;
            
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
            }
        }
    </script>
    <script>
        // Auto-populate price from service price list when property and service type are selected
        $(document).ready(function() {
            var propertyId = null;
            
            // Track property selection - handle both direct select and Choices.js
            $(document).on('change', '#property_id, select[name="property_id"]', function() {
                propertyId = $(this).val();
            });
            
            // Also track when Choices.js updates the value
            if (typeof Choices !== 'undefined') {
                $(document).on('change', 'select[name="property_id"]', function() {
                    var choicesInstance = Choices.getInstance(this);
                    if (choicesInstance) {
                        propertyId = choicesInstance.getValue(true);
                    }
                });
            }
            
            // When service type is selected in repeater rows, auto-fill price
            $(document).on('change', '.repeater select[name*="[invoice_type]"], .repeater select[name*="invoice_type"]', function() {
                var serviceTypeId = $(this).val();
                var $row = $(this).closest('tr');
                var amountInput = $row.find('input[name*="[amount]"], input[name*="amount"]');
                
                if (!propertyId) {
                    // Try to get property from the form
                    var $propertySelect = $('select[name="property_id"], #property_id');
                    if ($propertySelect.length) {
                        propertyId = $propertySelect.val();
                        // If Choices.js is used, try to get value differently
                        if (typeof Choices !== 'undefined') {
                            try {
                                var choicesInstance = Choices.getInstance($propertySelect[0]);
                                if (choicesInstance) {
                                    propertyId = choicesInstance.getValue(true);
                                }
                            } catch(e) {
                                // Choices instance not found
                            }
                        }
                    }
                }
                
                if (propertyId && serviceTypeId && amountInput.length) {
                    // Show loading state
                    amountInput.css('background-color', '#fff3cd');
                    amountInput.prop('disabled', true);
                    
                    $.ajax({
                        url: '{{ route("service-price-list.get-price") }}',
                        method: 'GET',
                        data: {
                            property_id: propertyId,
                            service_type_id: serviceTypeId
                        },
                        success: function(response) {
                            if (response.price && response.price > 0) {
                                amountInput.val(parseFloat(response.price).toFixed(2));
                                // Show visual feedback
                                amountInput.css('background-color', '#d4edda');
                                setTimeout(function() {
                                    amountInput.css('background-color', '');
                                }, 2000);
                                
                                // Show notification
                                if (typeof toastr !== 'undefined') {
                                    toastr.success('{{ __("Price auto-filled from service price list") }}');
                                }
                            } else {
                                amountInput.css('background-color', '#f8d7da');
                                setTimeout(function() {
                                    amountInput.css('background-color', '');
                                }, 2000);
                            }
                            amountInput.prop('disabled', false);
                        },
                        error: function(xhr) {
                            amountInput.css('background-color', '');
                            amountInput.prop('disabled', false);
                        }
                    });
                } else {
                    if (!propertyId) {
                        if (typeof toastr !== 'undefined') {
                            toastr.warning('{{ __("Please select a property first") }}');
                        }
                    }
                }
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('invoice.index') }}"> {{ __('Invoice') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Create') }}</li>
@endsection

@section('content')
    {{ Form::open(['url' => 'invoice', 'method' => 'post', 'id' => 'invoice_form']) }}
    <div class="row mt-4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="info-group">
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                                {{ Form::select('property_id', $property, null, ['class' => 'form-control hidesearch']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                                <div class="unit_div">
                                    <select class="form-control hidesearch unit" id="unit" name="unit_id">
                                        <option value="">{{ __('Select Unit') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <div class="form-group">
                                    {{ Form::label('invoice_id', __('Invoice Number'), ['class' => 'form-label']) }}
                                    <div class="input-group">
                                        <span class="input-group-text ">
                                            {{ invoicePrefix() }}
                                        </span>
                                        {{ Form::text('invoice_id', $invoiceNumber, ['class' => 'form-control', 'placeholder' => __('Enter Invoice Number')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('invoice_month', __('Invoice Month'), ['class' => 'form-label']) }}
                                {{ Form::month('invoice_month', null, ['class' => 'form-control', 'id' => 'invoice_month']) }}
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('end_date', __('Invoice End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', null, ['class' => 'form-control', 'id' => 'end_date']) }}
                            </div>
                            <div class="form-group col-md-12">
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                                {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter Notes')]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card repeater">
                <div class="card-header">

                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ __('Invoice Type') }}</h5>

                        <a class="btn btn-secondary d-flex align-items-center gap-2" href="#" data-repeater-create="">
                            <i class="ti ti-circle-plus align-text-bottom"></i>{{ __('Add Type') }}</a>

                    </div>

                </div>
                <div class="card-body">
                    <table class="display dataTable cell-border" data-repeater-list="types">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody data-repeater-item>
                            <tr>
                                <td width="30%">
                                    {{ Form::select('invoice_type', $types, null, ['class' => 'form-control hidesearch']) }}
                                </td>
                                <td>
                                    {{ Form::number('amount', null, ['class' => 'form-control']) }}
                                </td>
                                <td>
                                    {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 1]) }}
                                </td>
                                <td>
                                    <a class="text-danger" data-repeater-delete data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                            data-feather="trash-2"></i></a>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="group-button text-end">
                {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded', 'id' => 'invoice-submit']) }}
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection
