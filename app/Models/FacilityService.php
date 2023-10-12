<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_created_id',
        'user_updated_id',
        'user_closed_id',
        'doc_number',
        'flow_type',
        'room_type',
        'WO_type',
        'company',
        'location',
        'phone',
        'component_id',
        'request_description',
        'contact_reason',
        'report',
        'status',

    ];

    public function usersCreated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }

    public function usersUpdated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_updated_id');
    }

    public function serviceItem(): HasMany
    {
        return $this->hasMany(ServiceItem::class, 'facility_service_id');
    }

    public function attachmentFiles(): HasMany 
    {
        return $this->hasMany(AttachmentFile::class, 'facility_services_id');

    }

}
