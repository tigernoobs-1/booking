<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use App\Http\Resources\ConfigResource;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    //
    public function configTime()
    {
        $timeConfig = Config::where('config_name', 'operation_hour')->get();
        
        foreach ($timeConfig as $time) {
            $configValue = json_decode($time['config_value'], true);
            $facilityIds = $configValue['facility_id'];
            $items = Item::whereIn('id', $facilityIds)->pluck('name');
            $time['facility_name'] = $items;
        }
        
        $sortedData = collect($timeConfig)->sortBy(function ($item) {
            return json_decode($item['config_value'], true)['day'];
        })->values()->all();
        return ConfigResource::collection($sortedData);
        //return ConfigResource::collection(Config::where('config_name', 'operation_hour')->get());
    }

    public function configDay()
    {
        $dayConfig = Config::where('config_name', 'operation_day')->get();
        foreach ($dayConfig as $day) {
            $configValue = json_decode($day['config_value'], true);
            $facilityIds = $configValue['facility_id'];
            $items = Item::whereIn('id', $facilityIds)->pluck('name');
            $day['facility_name'] = $items;
        }
        return ConfigResource::collection($dayConfig);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'config_name' => 'required',
            'config_value' => 'required',
            'is_active' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        Config::create($input);
        return response()->json(['message' => 'data succesfully created']);
        
    }


    public function updateTimeConfig(Request $request, $id)
    {
        
        $config = Config::findOrFail($id);
        $input = $request->all();
        $config->update($input);
        return response()->json(['message' => 'Booking updated successfully.']);

    }

    public function destroy($id)
    {
        $config = Config::findOrFail($id);
        $config->delete();
        return response()->json(['message' => 'Config deleted successfully.']);
        //
    }
}
