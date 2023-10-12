<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
        'maximum_time',
        'display_dis',
        'dis_text',
        'limit_user',
        
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'item_id');
    }
}
