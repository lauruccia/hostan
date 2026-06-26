<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

  protected $fillable = [
    'property_id',
    'unit_id',
    'service_type',
    'maintainer_id',
    'rider_id',
    'status',
    'amount',
    'issue_attachment',
    'invoice',
    'notes',
    'admin_notes',
    'parent_id',
    'request_date',
    'fixed_date',
    'arrival_time',
    'arrival_type',
    'people_count',
    'started_at',
    'ended_at',
    'hours_worked',
    'completion_images',
];


    public static $status = [
        'pending'     => 'Pending',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
    ];

    // 🔹 Property relation
    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    // 🔹 Unit relation
    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    // 🔹 Service Type relation
    public function types()
    {
        return $this->hasOne(Type::class, 'id', 'service_type');
    }

    // 🔹 Maintainer relation (assigned operator / cleaning staff)
    public function maintainers()
    {
        return $this->hasOne(User::class, 'id', 'maintainer_id')->withDefault([
            'name' => 'Unassigned'
        ]);
    }

    // 🔹 Rider relation (optional second assignee for deliveries / laundry)
    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id', 'id');
    }

    // 🔹 Tenant relation (who lives in property+unit)
    public function tenetData()
    {
        return Tenant::where('property', $this->property_id)
                     ->where('unit', $this->unit_id)
                     ->first();
    }
}
