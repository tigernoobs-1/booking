<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_service_id',
        'item',
        'quantity',
        'price'
    ];

    public function itemService(): BelongsTo
    {
        return $this->belongsTo(FacilityService::class, 'facility_service_id');
    }
}
