<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachmentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_services_id',
        'file_name',
        'file_path',
    ];

    public function fileAttached(): BelongsTo
    {
        return $this->belongsTo(FacilityService::class, 'facility_services_id');
    }
}
