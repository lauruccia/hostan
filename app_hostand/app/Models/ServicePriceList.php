<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'owner_id',
        'price_1',
        'price_2',
        'price_3',
        'price_4',
        'price_5',
        'assigned_price_list',
        'parent_id',
    ];

    protected $casts = [
        'price_1' => 'decimal:2',
        'price_2' => 'decimal:2',
        'price_3' => 'decimal:2',
        'price_4' => 'decimal:2',
        'price_5' => 'decimal:2',
    ];

    // Relationship: Service Type
    public function serviceType()
    {
        return $this->belongsTo(Type::class, 'service_type_id', 'id');
    }

    // Relationship: Owner
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    // Get the assigned price based on assigned_price_list
    public function getAssignedPrice()
    {
        $priceField = 'price_' . $this->assigned_price_list;
        return $this->$priceField ?? 0.00;
    }

    // Scope: Get only templates (where owner_id is NULL)
    public function scopeTemplates($query)
    {
        return $query->whereNull('owner_id');
    }

    // Scope: Get only assignments (where owner_id is NOT NULL)
    public function scopeAssignments($query)
    {
        return $query->whereNotNull('owner_id');
    }

    // Check if this is a template
    public function isTemplate()
    {
        return is_null($this->owner_id);
    }

    // Check if this is an assignment
    public function isAssignment()
    {
        return !is_null($this->owner_id);
    }
}
