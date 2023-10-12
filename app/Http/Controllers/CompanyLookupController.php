<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyLookup;
use App\Http\Resources\CompanyLookupResource;

class CompanyLookupController extends Controller
{
    public function index() {
        return CompanyLookupResource::collection(CompanyLookup::all());
    }
}
