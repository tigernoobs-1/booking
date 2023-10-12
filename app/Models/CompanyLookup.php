<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyLookup extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_name',
        'company_group',
        'email_dns',
        'is_active_directory',
    ];
}
