<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Component;

class ComponentController extends Controller
{
    //

    public function index() {

        $componets =  Component::all();
        return response()->json($componets);

    }

   
}
