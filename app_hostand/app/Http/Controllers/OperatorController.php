<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Type;
use App\Models\Maintainer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class OperatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->type !== 'maintainer') {
                return redirect()->back()->with('error', __('Access Denied! Maintainer access required.'));
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $operator = Auth::user();
        $today = Carbon::today();
        
        // Get today's assigned services (operator is main assignee or rider)
        $todayServices = MaintenanceRequest::where(function ($q) use ($operator) {
            $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
        })
            ->whereNotNull('arrival_time')
            ->whereDate('arrival_time', $today)
            ->with(['properties', 'units', 'types'])
            ->orderBy('arrival_time', 'asc')
            ->get();

        // Get this week's services (by scheduled arrival time)
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        
        $weeklyServices = MaintenanceRequest::where(function ($q) use ($operator) {
            $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
        })
            ->whereNotNull('arrival_time')
            ->whereBetween('arrival_time', [$weekStart, $weekEnd])
            ->with(['properties', 'units', 'types'])
            ->orderBy('arrival_time', 'asc')
            ->get();

        // Get completed services this month (by scheduled arrival time)
        $monthStart = $today->copy()->startOfMonth();
        $completedServices = MaintenanceRequest::where(function ($q) use ($operator) {
            $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
        })
            ->where('status', 'completed')
            ->whereNotNull('arrival_time')
            ->whereBetween('arrival_time', [$monthStart, $today])
            ->with(['properties', 'units', 'types'])
            ->get();

        // Calculate statistics
        $totalCompleted = $completedServices->count();
        $totalHours = $completedServices->sum('hours_worked') ?? 0;
        $pendingToday = $todayServices->where('status', 'pending')->count();
        $inProgressToday = $todayServices->where('status', 'in_progress')->count();

        return view('operator.dashboard', compact(
            'todayServices',
            'weeklyServices', 
            'completedServices',
            'totalCompleted',
            'totalHours',
            'pendingToday',
            'inProgressToday',
            'operator'
        ));
    }

    public function dailyPlan()
    {
        $operator = Auth::user();
        $today = Carbon::today();
        
        // Get today's assigned services (operator is main assignee or rider)
        $dailyServices = MaintenanceRequest::where(function ($q) use ($operator) {
            $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
        })
            ->whereDate('arrival_time', $today)
            ->with(['properties', 'units', 'types'])
            ->orderBy('arrival_time', 'asc')
            ->get();

        // Group by status
        $pendingServices = $dailyServices->where('status', 'pending');
        $inProgressServices = $dailyServices->where('status', 'in_progress');
        $completedServices = $dailyServices->where('status', 'completed');

        return view('operator.daily-plan', compact(
            'dailyServices',
            'pendingServices',
            'inProgressServices', 
            'completedServices',
            'operator'
        ));
    }

    public function weeklyPlan(Request $request)
    {
        $operator = Auth::user();
        $today = Carbon::today();
        $defaultWeekStart = $today->copy()->startOfWeek();
        $defaultWeekEnd = $today->copy()->endOfWeek();

        // Date range: default to current week, or use filter params
        $startDateInput = $request->input('start_date', $defaultWeekStart->format('Y-m-d'));
        $endDateInput = $request->input('end_date', $defaultWeekEnd->format('Y-m-d'));

        $weekStart = Carbon::parse($startDateInput)->startOfDay();
        $weekEnd = Carbon::parse($endDateInput)->endOfDay();

        // Ensure start <= end
        if ($weekStart->gt($weekEnd)) {
            $weekEnd = $weekStart->copy()->endOfDay();
        }

        // Normalize locale (fixes "italian" vs "it")
        $locale = app()->getLocale();
        if ($locale === 'italian') {
            $locale = 'it';
        }

        Carbon::setLocale($locale);

        // Get services in the selected date range (operator is main assignee or rider)
        $weeklyServices = MaintenanceRequest::where(function ($q) use ($operator) {
            $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
        })
            ->whereBetween('arrival_time', [$weekStart, $weekEnd])
            ->with(['properties', 'units', 'types'])
            ->orderBy('arrival_time', 'asc')
            ->get();

        // Build one entry per day in the range
        $servicesByDay = [];
        $current = $weekStart->copy()->locale($locale);
        while ($current->lte($weekEnd)) {
            $dateKey = $current->format('Y-m-d');
            $dayServices = $weeklyServices->filter(function ($service) use ($current) {
                return Carbon::parse($service->arrival_time)->isSameDay($current);
            });
            $servicesByDay[$dateKey] = [
                'date' => $current->copy(),
                'services' => $dayServices,
                'hasServices' => $dayServices->count() > 0
            ];
            $current->addDay();
        }

        $totalDays = count($servicesByDay);

        return view('operator.weekly-plan', compact(
            'servicesByDay',
            'operator',
            'startDateInput',
            'endDateInput',
            'totalDays'
        ));
    }

    public function reports()
    {
        $operator = Auth::user();
        // dd($operator->first_name);
        $today = Carbon::today();
        
        // Get date range from request or default to current month
        $startDate = request('start_date', $today->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', $today->format('Y-m-d'));
        
        $start = Carbon::parse($startDate)->startOfDay()->setTimezone('UTC');
        $end = Carbon::parse($endDate)->endOfDay()->setTimezone('UTC');

        // Get all services in date range (operator is main assignee or rider)
        $allServices = MaintenanceRequest::where(function ($q) use ($operator) {
            $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
        })
            ->whereNotNull('arrival_time')
            ->whereBetween('arrival_time', [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')])
            ->with(['properties', 'units', 'types'])
            ->orderBy('arrival_time', 'desc')
            ->get();


           



        // Get completed services specifically
        $completedServices = $allServices->where('status', 'completed');

        // Calculate statistics
        $totalServices = $completedServices->count();
        $totalHours = $completedServices->sum('hours_worked') ?? 0;
        $totalAmount = $completedServices->sum('amount') ?? 0;
        
        // Also calculate overall statistics for better visibility
        $totalAllServices = $allServices->count();
        $pendingServices = $allServices->where('status', 'pending')->count();
        $inProgressServices = $allServices->where('status', 'in_progress')->count();
        
        // Group by property (all services)
        $servicesByProperty = $allServices->groupBy('property_id');
        
        // Group by week (all services) - using start of week as key based on arrival_time
        // arrival_time is stored as '2026-01-09 19:00:00' format in database
        $servicesByWeek = $allServices->groupBy(function ($service) {
            if (!empty($service->arrival_time)) {
                try {
                    // Parse the stored datetime format: '2026-01-09 19:00:00'
                    return Carbon::parse($service->arrival_time, 'UTC')->startOfWeek()->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning('Failed to parse arrival_time for week grouping: ' . $service->id . ' - ' . $e->getMessage());
                    return 'no-date';
                }
            }
            return 'no-date';
        });

        return view('operator.reports', compact(
            'completedServices',
            'allServices',
            'totalServices',
            'totalHours',
            'totalAmount',
            'totalAllServices',
            'pendingServices',
            'inProgressServices',
            'servicesByProperty',
            'servicesByWeek',
            'startDate',
            'endDate',
            'operator'
        ));
    }

    public function updateServiceStatus(Request $request, $serviceId)
    {
        $operator = Auth::user();
        $service = MaintenanceRequest::where('id', $serviceId)
            ->where(function ($q) use ($operator) {
                $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
            })
            ->firstOrFail();

        Log::info('=== updateServiceStatus Debug ===');
        Log::info('Service ID: ' . $serviceId);
        Log::info('Requested Status: ' . $request->status);
        Log::info('Current Status: ' . $service->status);
        Log::info('Arrival Time (raw from DB): ' . ($service->arrival_time ?? 'NULL'));

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'hours_worked' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'completion_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // Max 5MB per image
        ]);

        // ✅ Validate: Status can only be changed if the arrival date has been reached
        if ($service->arrival_time && $request->status !== 'pending') {
            // Get user's timezone from settings
            $settings = settings();
            $timezone = !empty($settings['timezone']) && $settings['timezone'] !== '' ? $settings['timezone'] : 'UTC';
            
            // Parse arrival_time (stored in UTC) and convert to user's timezone
            $arrivalTimeUTC = Carbon::parse($service->arrival_time, 'UTC');
            $arrivalTimeLocal = $arrivalTimeUTC->copy()->setTimezone($timezone);
            $currentDateLocal = Carbon::now($timezone);
            
            // Get just the date (without time) for comparison
            $arrivalDate = $arrivalTimeLocal->copy()->startOfDay();
            $currentDate = $currentDateLocal->copy()->startOfDay();
            
            Log::info('--- Date Validation Debug ---');
            Log::info('User Timezone: ' . $timezone);
            Log::info('Arrival Date (Local): ' . $arrivalDate->format('Y-m-d'));
            Log::info('Current Date (Local): ' . $currentDate->format('Y-m-d'));
            Log::info('Is Current Date >= Arrival Date? ' . ($currentDate->gte($arrivalDate) ? 'YES' : 'NO'));
            
            // If current date is before arrival date, reject the status change
            if ($currentDate->lt($arrivalDate)) {
                Log::warning('Status change REJECTED - Arrival date has not been reached');
                return redirect()->back()->with('error', __('Status will be changed once the date is reached.'));
            }
            
            Log::info('Status change ALLOWED - Arrival date has been reached');
        } else {
            Log::info('Validation skipped - arrival_time: ' . ($service->arrival_time ? 'exists' : 'NULL') . ', status: ' . $request->status);
        }

        // Handle completion images upload when status is 'completed'
        $completionImages = [];
        if ($request->status === 'completed' && $request->hasFile('completion_images')) {
            // Get existing images if any
            $existingImages = $service->completion_images ? json_decode($service->completion_images, true) : [];
            if (!is_array($existingImages)) {
                $existingImages = [];
            }

            // Create directory if it doesn't exist (in public/uploads/facility_images)
            $uploadDir = public_path('uploads/facility_images');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Upload each image
            foreach ($request->file('completion_images') as $image) {
                $filenameWithExt = $image->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $image->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '_' . uniqid() . '.' . $extension;
                
                // Store the image in public/uploads/facility_images
                $image->move($uploadDir, $fileNameToStore);
                $completionImages[] = 'uploads/facility_images/' . $fileNameToStore;
            }

            // Merge with existing images
            $completionImages = array_merge($existingImages, $completionImages);
        } else {
            // Keep existing images if status is not 'completed' or no new images uploaded
            $completionImages = $service->completion_images ? json_decode($service->completion_images, true) : [];
            if (!is_array($completionImages)) {
                $completionImages = [];
            }
        }

        $updateData = [
            'status' => $request->status,
            'hours_worked' => $request->filled('hours_worked') 
                    ? $request->hours_worked 
                    : 0,
            'operator_notes' => $request->notes,
            'completed_at' => $request->status === 'completed' ? now() : null
        ];

        // Only update completion_images if we have images or status is completed
        if (!empty($completionImages) || $request->status === 'completed') {
            $updateData['completion_images'] = json_encode($completionImages);
        }

        $service->update($updateData);
        
            $this->startTimer($service->id);
       


        if($service->started_at != null)
        {
            $this->stopTimer($service->id);
        }

        return redirect()->back()->with('success', __('Service status updated successfully!'));
    }

    public function startTimer($serviceId)
    {
        $operator = Auth::user();
        $service = MaintenanceRequest::where('id', $serviceId)
            ->where(function ($q) use ($operator) {
                $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
            })
            ->firstOrFail();

        // if ($service->status !== 'in_progress') {
        //     return redirect()->back()->with('error', __('Service must be in progress to start timer.'));
        // }

        // Only set started_at if not already set
        if (!$service->started_at) {
            $service->started_at = now();
            $service->save();
        }

        return redirect()->back()->with('success', __('Service started successfully!'));
    }

    public function stopTimer($serviceId)
    {
        $operator = Auth::user();
        $service = MaintenanceRequest::where('id', $serviceId)
            ->where(function ($q) use ($operator) {
                $q->where('maintainer_id', $operator->id)->orWhere('rider_id', $operator->id);
            })
            ->firstOrFail();

        if (!$service->started_at) {
            return redirect()->back()->with('error', __('Service has not been started.'));
        }

        $startTime = Carbon::parse($service->started_at);
        $endTime = now();

        // Use fractional hours
        $hoursWorked = $startTime->floatDiffInHours($endTime);

        // Update fields
        $service->ended_at = $endTime;
        $service->hours_worked = ($service->hours_worked ?? 0) + $hoursWorked;

        // Debug: Uncomment if needed
        // dd($service);

        $service->save();

        return redirect()->back()->with('success', __('Service ended. Hours recorded: ' . number_format($hoursWorked, 2)));
    }


} 