<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\Maintainer;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaintenanceRequestController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage maintenance request') || \Auth::user()->type == 'admin') {
            if (\Auth::user()->type == 'maintainer') {
                $maintenanceRequests = MaintenanceRequest::where(function ($q) {
                    $q->where('maintainer_id', \Auth::user()->id)->orWhere('rider_id', \Auth::user()->id);
                })->get();
            } elseif (\Auth::user()->type == 'tenant') {
                $user = \Auth::user();
                $tenant = $user->tenants;
                $maintenanceRequests = MaintenanceRequest::where('property_id', !empty($tenant) ? $tenant->property : 0)
                    ->where('unit_id', !empty($tenant) ? $tenant->unit : 0)
                    ->get();
            } else {
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->get();
            }

            $maintenanceRequests->load([
                'properties' => function ($query) {
                    $query->with(['propertyImages', 'totalUnits']);
                },
                'units',
                'types',
                'maintainers',
                'rider'
            ]);

            $events = $maintenanceRequests->map(function ($request) {
                $property = $request->properties;
                if (!$property && $request->property_id) {
                    $property = Property::where('id', $request->property_id)->first();
                }

                $unit = $request->units;
                if (!$unit && $request->unit_id) {
                    $unit = \App\Models\PropertyUnit::where('id', $request->unit_id)->first();
                }

                $propertyName = $property->name ?? 'Unknown Property';
                $unitName = $unit->name ?? 'Unknown Unit';

                $title = $propertyName;
                if ($request->arrival_time) {
                    $title .= ' (' . Carbon::parse($request->arrival_time)->format('h:i A') . ')';
                }

                return [
                    'id' => $request->id,
                    'title' => $title,
                    'start' => $request->arrival_time,
                    'extendedProps' => [
                        'property_id' => $request->property_id,
                        'property_name' => $propertyName,
                        'unit_id' => $request->unit_id,
                        'unit_name' => $unitName,
                        'maintainer' => $request->maintainers->name ?? '-',
                        'status' => $request->status,
                        'attachment' => $request->issue_attachment,
                        'people_count' => $request->people_count,
                        'arrival_time' => $request->arrival_time,
                        'show_url' => route('maintenance-request.show', $request->id),
                        'edit_url' => route('maintenance-request.edit', $request->id),
                        'delete_url' => route('maintenance-request.destroy', $request->id),
                        'status_url' => route('maintenance-request.action', $request->id),
                    ]
                ];
            });

            return view('maintenance_request.index', compact('events'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create maintenance request')) {
            if (Auth::user()->type == "super admin") {
                $property = Property::pluck('name', 'id');
            } else {
                $property = Property::where('parent_id', parentId())->pluck('name', 'id');
            }

            $property->prepend(__('Select Property'), 0);
            $services = Type::where('type', 'issue')->pluck('title', 'id');
            $peopleCount = [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7+'];

            return view('maintenance_request.create', compact('property', 'services', 'peopleCount'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

     public function store(Request $request)
    {
        if (\Auth::user()->can('create maintenance request')) {
            $validator = \Validator::make($request->all(), [
                'property_id' => 'required',
                'unit_id' => 'required',
                'service' => 'required',
                'arrival_type' => 'required',
                'arrival_time' => 'required|date',
                'people_count' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $settings = settings();
            $timezone = !empty($settings['timezone']) ? trim($settings['timezone']) : config('app.timezone', 'UTC');
            if (empty($timezone)) {
                $timezone = 'UTC';
            }

            // Maintenance Blocking Logic (Non-admins only)
            $userType = strtolower(\Auth::user()->type);
            if (!in_array($userType, ['admin', 'super admin'])) {
                $cutoffTimeSetting = !empty($settings['maintenance_cutoff_time']) ? $settings['maintenance_cutoff_time'] : '18:30';
                $cutoffTime = Carbon::parse($cutoffTimeSetting)->format('H:i');
                $now = Carbon::now($timezone);
                $requestedDate = Carbon::parse($request->arrival_time, $timezone)->startOfDay();
                $today = $now->copy()->startOfDay();
                $tomorrow = $now->copy()->addDay()->startOfDay();

                // 1. Block Same Day
                if ($requestedDate->isSameDay($today)) {
                    $msg = __('You cannot add services for the next day after :time. Only administrators can perform this action.', ['time' => $cutoffTimeSetting]);
                    return redirect()->back()->withErrors(['maintenance_cutoff' => $msg])->with('error', $msg);
                }

                // 2. 6:30 PM Cut-off for next day
                if ($now->format('H:i') > $cutoffTime && $requestedDate->isSameDay($tomorrow)) {
                    $msg = __('You cannot add services for the next day after :time. Only administrators can perform this action.', ['time' => $cutoffTimeSetting]);
                    return redirect()->back()->withErrors(['maintenance_cutoff' => $msg])->with('error', $msg);
                }

                // 3. Block Multiple Services for the same day/unit
                $exists = MaintenanceRequest::where('property_id', $request->property_id)
                    ->where('unit_id', $request->unit_id)
                    ->whereDate('arrival_time', $requestedDate->toDateString())
                    ->exists();

                if ($exists) {
                    $msg = __('A service request already exists for this date. Only one request is allowed per day.');
                    return redirect()->back()->with('error', $msg);
                }
            }

            $arrivalTime = null;
            if ($request->arrival_time) {
                $arrivalTime = Carbon::parse($request->arrival_time, $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            }

            $MaintenanceRequest = new MaintenanceRequest();
            $MaintenanceRequest->property_id = $request->property_id;
            $MaintenanceRequest->unit_id = $request->unit_id;
            $MaintenanceRequest->service_type = $request->service;
            $MaintenanceRequest->arrival_type = $request->arrival_type;
            $MaintenanceRequest->arrival_time = $arrivalTime;
            $MaintenanceRequest->request_date = now()->toDateTimeString();
            $MaintenanceRequest->people_count = $request->people_count;
            $MaintenanceRequest->notes = $request->notes;
            $MaintenanceRequest->parent_id = parentId();
            $MaintenanceRequest->status = 'pending';
            $MaintenanceRequest->save();

            $errorMessage = '';
            if (Auth::user()->type === 'owner') {
                $adminNotificationResponse = sendAdminNotificationForOwnerAction('create', $MaintenanceRequest);
                if ($adminNotificationResponse['status'] == 'error') {
                    $errorMessage = $adminNotificationResponse['message'];
                }
            }

            return redirect()->back()->with('success', __('Maintenance request successfully created.') . ($errorMessage ? '</br>' . $errorMessage : ''));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['properties.thumbnail', 'properties.propertyImages', 'properties.totalUnits', 'units', 'types', 'maintainers', 'rider']);
        return response()->view('maintenance_request.show', compact('maintenanceRequest'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('edit maintenance request')) {
            $property = (Auth::user()->type == "super admin")
                ? Property::pluck('name', 'id')
                : Property::where('parent_id', parentId())->pluck('name', 'id');

            $property->prepend(__('Select Property'), 0);

            $maintainers = (Auth::user()->type == "super admin")
                ? User::where('type', 'maintainer')->select(DB::raw("CONCAT_WS(' ', first_name, last_name) as name"), 'id')->pluck('name', 'id')
                : User::where('parent_id', parentId())->where('type', 'maintainer')->select(DB::raw("CONCAT_WS(' ', first_name, last_name) as name"), 'id')->pluck('name', 'id');

            $maintainers->prepend(__('Select Maintainer'), 0);
            $types = Type::where('type', 'issue')->pluck('title', 'id')->prepend(__('Select Type'), '');
            $peopleCount = [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7+'];
            $status = MaintenanceRequest::$status;

            return view('maintenance_request.edit', compact('property', 'types', 'maintainers', 'maintenanceRequest', 'status', 'peopleCount'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

      public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if (!\Auth::user()->can('edit maintenance request')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $validator = \Validator::make($request->all(), [
            'property_id' => 'required',
            'unit_id' => 'required',
            'issue_type' => 'required',
            'maintainer_id' => Auth::user()->type != 'owner' ? 'required' : 'nullable',
            'people_count' => 'nullable|in:1,2,3,4,5,6,7',
            'arrival_type' => 'required',
            'arrival_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $settings = settings();
        $timezone = !empty($settings['timezone']) ? trim($settings['timezone']) : config('app.timezone', 'UTC');
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        // 6:30 PM Cut-off for next day (Non-admins only)
        $userType = strtolower(\Auth::user()->type);
        if (!in_array($userType, ['admin', 'super admin'])) {
            $cutoffTimeSetting = !empty($settings['maintenance_cutoff_time']) ? $settings['maintenance_cutoff_time'] : '18:30';
            $cutoffTime = Carbon::parse($cutoffTimeSetting)->format('H:i');
            $now = Carbon::now($timezone);
            $requestedDate = Carbon::parse($request->arrival_time, $timezone)->startOfDay();
            $today = $now->copy()->startOfDay();
            $tomorrow = $now->copy()->addDay()->startOfDay();

            if ($requestedDate->isSameDay($today)) {
                $msg = __('You cannot modify services for the next day after :time. Only administrators can perform this action.', ['time' => $cutoffTimeSetting]);
                return redirect()->back()->withErrors(['maintenance_cutoff' => $msg])->with('error', $msg);
            }

            if ($now->format('H:i') > $cutoffTime && $requestedDate->isSameDay($tomorrow)) {
                $msg = __('You cannot modify services for the next day after :time. Only administrators can perform this action.', ['time' => $cutoffTimeSetting]);
                return redirect()->back()->withErrors(['maintenance_cutoff' => $msg])->with('error', $msg);
            }

            // 3. Block Multiple Services for the same day/unit (excluding current request)
            $exists = MaintenanceRequest::where('property_id', $request->property_id)
                ->where('unit_id', $request->unit_id)
                ->whereDate('arrival_time', $requestedDate->toDateString())
                ->where('id', '!=', $maintenanceRequest->id)
                ->exists();

            if ($exists) {
                $msg = __('A service request already exists for this date. Only one request is allowed per day.');
                return redirect()->back()->with('error', $msg);
            }
        }

        $arrivalTime = null;
        if ($request->filled('arrival_time')) {
            $arrivalTime = Carbon::parse($request->arrival_time, $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
        }

        // Status change validation (only if current date >= arrival date)
        if ($request->filled('status') && $request->status !== $maintenanceRequest->status && $request->status !== 'pending') {
            $checkTime = $arrivalTime ?: $maintenanceRequest->arrival_time;
            if ($checkTime) {
                $arrivalDate = Carbon::parse($checkTime, 'UTC')->setTimezone($timezone)->startOfDay();
                if (Carbon::now($timezone)->startOfDay()->lt($arrivalDate)) {
                    return redirect()->back()->with('error', __('Status will be changed once the date is reached.'));
                }
            }
        }

        $maintenanceRequest->property_id = $request->property_id;
        $maintenanceRequest->unit_id = $request->unit_id;
        $maintenanceRequest->service_type = $request->issue_type;
        $maintenanceRequest->maintainer_id = (Auth::user()->type == 'owner') ? $maintenanceRequest->maintainer_id : $request->maintainer_id;
        $maintenanceRequest->people_count = $request->people_count ?? $maintenanceRequest->people_count;
        $maintenanceRequest->arrival_type = $request->arrival_type;
        $maintenanceRequest->arrival_time = $arrivalTime;
        $maintenanceRequest->status = $request->status ?? $maintenanceRequest->status;
        $maintenanceRequest->notes = $request->notes;
        $maintenanceRequest->save();

        if ($request->hasFile('issue_attachment')) {
            $fileName = pathinfo($request->file('issue_attachment')->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.' . $request->file('issue_attachment')->getClientOriginalExtension();
            $request->file('issue_attachment')->storeAs('upload/issue_attachment/', $fileName);
            $maintenanceRequest->issue_attachment = $fileName;
            $maintenanceRequest->save();
        }

        $errorMessage = '';
        if (Auth::user()->type === 'owner') {
            $adminRes = sendAdminNotificationForOwnerAction('update', $maintenanceRequest);
            if ($adminRes['status'] == 'error')
                $errorMessage = $adminRes['message'];
        }

        return redirect()->back()->with('success', __('Maintenance request successfully updated.') . ($errorMessage ? '</br>' . $errorMessage : ''));
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('delete maintenance request')) {
            $maintenanceRequest->load(['properties', 'units', 'types', 'maintainers']);
            $errorMessage = '';

            if (Auth::user()->type === 'owner') {
                $adminRes = sendAdminNotificationForOwnerAction('delete', $maintenanceRequest);
                if ($adminRes['status'] == 'error')
                    $errorMessage = $adminRes['message'];
            }

            $maintenanceRequest->delete();
            return redirect()->back()->with('success', __('Maintenance request successfully deleted.') . ($errorMessage ? '</br>' . $errorMessage : ''));
        }
        return redirect()->back()->with('error', __('Permission Denied!'));
    }

    public function action($id)
    {
        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $maintenanceRequest->load(['properties', 'units', 'types', 'maintainers']);
        $status = MaintenanceRequest::$status;
        return view('maintenance_request.action', compact('maintenanceRequest', 'status'));
    }

       public function actionData(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'arrival_time' => 'required',
            'status' => 'required',
            'completion_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        if ($validator->fails())
            return redirect()->back()->with('error', $validator->errors()->first());

        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $settings = settings();
        $timezone = !empty($settings['timezone']) ? trim($settings['timezone']) : config('app.timezone', 'UTC');
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        // Maintenance Blocking Logic (Non-admins only)
        $userType = strtolower(\Auth::user()->type);
        if (!in_array($userType, ['admin', 'super admin'])) {
            $cutoffTimeSetting = !empty($settings['maintenance_cutoff_time']) ? $settings['maintenance_cutoff_time'] : '18:30';
            $cutoffTime = Carbon::parse($cutoffTimeSetting)->format('H:i');
            $now = Carbon::now($timezone);
            $requestedDate = Carbon::parse($request->arrival_time, $timezone)->startOfDay();
            $today = $now->copy()->startOfDay();
            $tomorrow = $now->copy()->addDay()->startOfDay();

            // 1. Block Same Day
            if ($requestedDate->isSameDay($today)) {
                $msg = __('You cannot modify services for the next day after :time. Only administrators can perform this action.', ['time' => $cutoffTimeSetting]);
                return redirect()->back()->withErrors(['maintenance_cutoff' => $msg])->with('error', $msg);
            }

            // 2. 6:30 PM Cut-off for next day
            if ($now->format('H:i') > $cutoffTime && $requestedDate->isSameDay($tomorrow)) {
                $msg = __('You cannot modify services for the next day after :time. Only administrators can perform this action.', ['time' => $cutoffTimeSetting]);
                return redirect()->back()->withErrors(['maintenance_cutoff' => $msg])->with('error', $msg);
            }

            // 3. Block Multiple Services for the same day/unit (excluding current request)
            $exists = MaintenanceRequest::where('property_id', $maintenanceRequest->property_id)
                ->where('unit_id', $maintenanceRequest->unit_id)
                ->whereDate('arrival_time', $requestedDate->toDateString())
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                $msg = __('A service request already exists for this date. Only one request is allowed per day.');
                return redirect()->back()->with('error', $msg);
            }
        }

        $arrivalTime = null;
        if ($request->filled('arrival_time')) {
            $arrivalTime = Carbon::parse($request->arrival_time, $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
        }

        $checkTime = $arrivalTime ?: $maintenanceRequest->arrival_time;
        if ($checkTime && $request->status !== 'pending') {
            $arrivalDate = Carbon::parse($checkTime, 'UTC')->setTimezone($timezone)->startOfDay();
            if (Carbon::now($timezone)->startOfDay()->lt($arrivalDate)) {
                return redirect()->back()->with('error', __('Status will be changed once the date is reached.'));
            }
        }

        if ($arrivalTime) {
            $maintenanceRequest->arrival_time = $arrivalTime;
            $maintenanceRequest->fixed_date = Carbon::parse($request->arrival_time)->format('Y-m-d');
        }

        $maintenanceRequest->status = $request->status;
        $maintenanceRequest->save();

        return redirect()->back()->with('success', __('Maintenance request successfully update.'));
    }

    public function pendingRequest()
    {
        return view('maintenance_request.type', ['maintenanceRequests' => $this->getMaintenanceRequestsByStatus('pending')]);
    }
    public function inProgressRequest()
    {
        return view('maintenance_request.type', ['maintenanceRequests' => $this->getMaintenanceRequestsByStatus('in_progress')]);
    }
    public function completed()
    {
        return view('maintenance_request.type', ['maintenanceRequests' => $this->getMaintenanceRequestsByStatus('completed')]);
    }

    private function getMaintenanceRequestsByStatus($status)
    {
        if (!\Auth::user()->can('manage maintenance request'))
            return [];

        $query = MaintenanceRequest::where('status', $status);
        if (\Auth::user()->type == 'maintainer') {
            $query->where(function ($q) {
                $q->where('maintainer_id', \Auth::user()->id)->orWhere('rider_id', \Auth::user()->id);
            });
        } elseif (\Auth::user()->type == 'tenant') {
            $tenant = \Auth::user()->tenants;
            $query->where('property_id', $tenant->property ?? 0)->where('unit_id', $tenant->unit ?? 0);
        } else {
            $query->where('parent_id', parentId());
        }

        return $query->with(['properties', 'units', 'types', 'maintainers'])->get();
    }
}
