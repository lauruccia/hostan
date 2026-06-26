<?php

namespace App\Http\Controllers;

use App\Models\ServicePriceList;
use App\Models\Type;
use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServicePriceListController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type != 'super admin' && !\Auth::user()->can('manage finance')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // Get all service types (both 'invoice' and 'issue' types)
        $serviceTypes = Type::whereIn('type', ['invoice', 'issue'])
            ->orderBy('title', 'asc')
            ->get();

        // Get all owners
        if (\Auth::user()->type == 'super admin') {
            $owners = User::where('type', 'owner')->orderBy('first_name', 'asc')->get();
        } else {
            $owners = User::where('type', 'owner')
                ->where('parent_id', parentId())
                ->orderBy('first_name', 'asc')
                ->get();
        }

        // Get all price templates (where owner_id is NULL)
        $templates = ServicePriceList::where('parent_id', parentId())
            ->templates()
            ->with('serviceType')
            ->get()
            ->keyBy('service_type_id');

        // Get all assignments (where owner_id is NOT NULL)
        $assignments = ServicePriceList::where('parent_id', parentId())
            ->assignments()
            ->with(['serviceType', 'owner'])
            ->get();

        return view('finance.service-price-list.index', compact('serviceTypes', 'owners', 'templates', 'assignments'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type != 'super admin' && !\Auth::user()->can('manage finance')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        // Check if this is a template (no owner) or assignment (with owner)
        if ($request->has('is_template') && $request->is_template == '1') {
            return $this->storeTemplate($request);
        } else {
            return $this->storeAssignment($request);
        }
    }

    // Store price template (service + prices, no owner)
    private function storeTemplate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_type_id' => 'required|exists:types,id',
            'price_1' => 'required|numeric|min:0',
            'price_2' => 'required|numeric|min:0',
            'price_3' => 'required|numeric|min:0',
            'price_4' => 'required|numeric|min:0',
            'price_5' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // Check if template already exists for this service
        $existingTemplate = ServicePriceList::where('service_type_id', $request->service_type_id)
            ->whereNull('owner_id')
            ->where('parent_id', parentId())
            ->first();

        if ($existingTemplate) {
            return redirect()->back()->with('error', __('A price template already exists for this service type. Please edit the existing template.'));
        }

        ServicePriceList::create([
            'service_type_id' => $request->service_type_id,
            'owner_id' => null,
            'parent_id' => parentId(),
            'price_1' => $request->price_1,
            'price_2' => $request->price_2,
            'price_3' => $request->price_3,
            'price_4' => $request->price_4,
            'price_5' => $request->price_5,
            'assigned_price_list' => 1, // Default to price list 1 for templates
        ]);

        return redirect()->back()->with('success', __('Service price template created successfully.'));
    }

    // Store assignment (assign template to owner)
    private function storeAssignment(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'service_type_id' => 'required|exists:types,id',
            'owner_id' => 'required|exists:users,id',
            'assigned_price_list' => 'required|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // Get the template for this service
        $template = ServicePriceList::where('service_type_id', $request->service_type_id)
            ->whereNull('owner_id')
            ->where('parent_id', parentId())
            ->first();

        if (!$template) {
            return redirect()->back()->with('error', __('Please create a price template for this service type first.'));
        }

        // Check if assignment already exists
        $existingAssignment = ServicePriceList::where('service_type_id', $request->service_type_id)
            ->where('owner_id', $request->owner_id)
            ->where('parent_id', parentId())
            ->first();

        if ($existingAssignment) {
            return redirect()->back()->with('error', __('This owner already has a price list assigned for this service. Please edit the existing assignment.'));
        }

        // Create assignment with prices from template
        ServicePriceList::create([
            'service_type_id' => $request->service_type_id,
            'owner_id' => $request->owner_id,
            'parent_id' => parentId(),
            'price_1' => $template->price_1,
            'price_2' => $template->price_2,
            'price_3' => $template->price_3,
            'price_4' => $template->price_4,
            'price_5' => $template->price_5,
            'assigned_price_list' => $request->assigned_price_list,
        ]);

        return redirect()->back()->with('success', __('Price list assigned to owner successfully.'));
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->type != 'super admin' && !\Auth::user()->can('manage finance')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $priceList = ServicePriceList::where('id', $id)
            ->where('parent_id', parentId())
            ->firstOrFail();

        // If it's a template, update prices
        if ($priceList->isTemplate()) {
            $validator = \Validator::make($request->all(), [
                'price_1' => 'required|numeric|min:0',
                'price_2' => 'required|numeric|min:0',
                'price_3' => 'required|numeric|min:0',
                'price_4' => 'required|numeric|min:0',
                'price_5' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $priceList->update([
                'price_1' => $request->price_1,
                'price_2' => $request->price_2,
                'price_3' => $request->price_3,
                'price_4' => $request->price_4,
                'price_5' => $request->price_5,
            ]);

            return redirect()->back()->with('success', __('Service price template updated successfully.'));
        } else {
            // If it's an assignment, only update assigned_price_list
            $validator = \Validator::make($request->all(), [
                'assigned_price_list' => 'required|in:1,2,3,4,5',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $priceList->update([
                'assigned_price_list' => $request->assigned_price_list,
            ]);

            return redirect()->back()->with('success', __('Price list assignment updated successfully.'));
        }
    }

    // API endpoint to get price for invoice creation
    public function getPrice(Request $request)
    {
        $serviceTypeId = $request->get('service_type_id');
        $propertyId = $request->get('property_id');

        if (!$serviceTypeId || !$propertyId) {
            return response()->json(['price' => 0]);
        }

        // Get property owner
        $property = Property::find($propertyId);
        if (!$property) {
            return response()->json(['price' => 0]);
        }

        $ownerId = $property->parent_id; // Property belongs to owner

        // Get price list assignment for this owner
        $priceList = ServicePriceList::where('service_type_id', $serviceTypeId)
            ->where('owner_id', $ownerId)
            ->where('parent_id', parentId())
            ->first();

        if (!$priceList) {
            return response()->json(['price' => 0]);
        }

        return response()->json([
            'price' => $priceList->getAssignedPrice(),
            'price_list_number' => $priceList->assigned_price_list,
        ]);
    }

    // Get templates for a service type (AJAX)
    public function getTemplates(Request $request)
    {
        $serviceTypeId = $request->get('service_type_id');
        
        if (!$serviceTypeId) {
            return response()->json(['templates' => []]);
        }

        $template = ServicePriceList::where('service_type_id', $serviceTypeId)
            ->whereNull('owner_id')
            ->where('parent_id', parentId())
            ->first();

        if (!$template) {
            return response()->json(['templates' => []]);
        }

        return response()->json([
            'template' => [
                'id' => $template->id,
                'price_1' => $template->price_1,
                'price_2' => $template->price_2,
                'price_3' => $template->price_3,
                'price_4' => $template->price_4,
                'price_5' => $template->price_5,
            ]
        ]);
    }

    // Get services for invoice creation based on property, unit and date range
    public function getServicesForInvoice(Request $request)
    {
        $propertyId = $request->get('property_id');
        $unitId = $request->get('unit_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$propertyId || !$startDate || !$endDate) {
            return response()->json([
                'services' => [],
                'debug' => 'Missing required parameters',
                'params' => [
                    'property_id' => $propertyId,
                    'unit_id' => $unitId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
        }
        
        // Get property owner
        $property = Property::find($propertyId);
        if (!$property) {
            return response()->json(['services' => [], 'debug' => 'Property not found']);
        }

        $ownerId = $property->parent_id;
        
        // Build query for maintenance requests
        $query = \App\Models\MaintenanceRequest::where('property_id', $propertyId);

        // Add unit filter if provided
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        // Filter by service_type - must not be null and not 0
        $query->whereNotNull('service_type')
              ->where('service_type', '!=', 0);

        // Filter by arrival_time date range - arrival_time is stored as datetime (e.g., 2026-01-12 15:41:58)
        // Compare dates from arrival_time field
        $query->whereNotNull('arrival_time')
              ->whereDate('arrival_time', '>=', $startDate)
              ->whereDate('arrival_time', '<=', $endDate);

        // Get all matching records first
        $allMatchingRequests = $query->with('types')->orderBy('arrival_time', 'asc')->get();
        
        // Now filter by parent_id - match maintenance request's parent_id with property owner_id
        // The parent_id in maintenance_requests represents the owner who created the request
        // We should match it with the property's owner_id, not the logged-in user's parent_id
        $maintenanceRequests = $allMatchingRequests->filter(function($request) use ($ownerId) {
            // Match maintenance request's parent_id with property owner_id
            // Also allow parent_id = 0 (system/global requests)
            return $request->parent_id == $ownerId || $request->parent_id == 0;
        });

        // Debug info - include both filtered and unfiltered counts
        $debugInfo = [
            'total_requests_found' => $maintenanceRequests->count(),
            'total_requests_before_parent_filter' => $allMatchingRequests->count(),
            'property_id' => $propertyId,
            'unit_id' => $unitId,
            'owner_id' => $ownerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'logged_in_parent_id' => parentId(),
            'sample_request' => $maintenanceRequests->first() ? [
                'id' => $maintenanceRequests->first()->id,
                'property_id' => $maintenanceRequests->first()->property_id,
                'unit_id' => $maintenanceRequests->first()->unit_id,
                'service_type' => $maintenanceRequests->first()->service_type,
                'arrival_time' => $maintenanceRequests->first()->arrival_time,
                'request_date' => $maintenanceRequests->first()->request_date,
                'parent_id' => $maintenanceRequests->first()->parent_id,
            ] : null,
            'all_matching_requests' => $allMatchingRequests->take(5)->map(function($r) {
                return [
                    'id' => $r->id,
                    'property_id' => $r->property_id,
                    'unit_id' => $r->unit_id,
                    'service_type' => $r->service_type,
                    'arrival_time' => $r->arrival_time,
                    'parent_id' => $r->parent_id,
                ];
            })->toArray()
        ];

        $services = [];

        foreach ($maintenanceRequests as $request) {
            // Handle both service_type and issue_type field names
            $serviceTypeId = $request->service_type ?? $request->issue_type ?? null;
            
            if (!$serviceTypeId || $serviceTypeId == 0) {
                continue;
            }

            // Get price for this service type and owner from assigned price list
            $priceList = ServicePriceList::where('service_type_id', $serviceTypeId)
                ->where('owner_id', $ownerId)
                ->where('parent_id', parentId())
                ->whereNotNull('owner_id') // Must be an assignment, not a template
                ->first();

            $price = 0;
            if ($priceList) {
                $price = $priceList->getAssignedPrice();
            }

            // Add each service request as a separate invoice item
            $services[] = [
                'service_type_id' => $serviceTypeId,
                'service_type_name' => $request->types->title ?? 'N/A',
                'price' => $price,
                'description' => $request->notes ?? '',
                'arrival_time' => $request->arrival_time ? date('Y-m-d H:i:s', strtotime($request->arrival_time)) : '',
                'request_date' => $request->request_date ? date('Y-m-d', strtotime($request->request_date)) : '',
            ];
        }
        
        return response()->json([
            'services' => $services
        ]);
    }
}
