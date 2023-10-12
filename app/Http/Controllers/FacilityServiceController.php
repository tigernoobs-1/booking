<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacilityService;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FacilityServiceResource;
use App\Models\Component;
use App\Models\ServiceItem;
use App\Models\AttachmentFile;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Traits\EmailTraits;

class FacilityServiceController extends Controller
{
    use EmailTraits;
    //

    public function indexAdmin()
    {
        $user = Auth::user();
        if ($user->role == 'Admin' || $user->role == 'admin-facility') {
            $services = FacilityService::with('usersCreated:name,id,email')
                ->with('usersUpdated:name,id,email')
                ->where('flow_type', 'service_request')
                ->get();
            foreach ($services as $service) {
                $component_id = json_decode($service['component_id'], true);
                $component_name = Component::whereIn('id', $component_id)->pluck('title');
                $service['component_name'] = $component_name;
            }
        }

        return FacilityServiceResource::collection($services);
    }

    public function indexWorkAdmin()
    {
        $user = Auth::user();
        if ($user->role == 'Admin' || $user->role == 'admin-facility') {
            $services = FacilityService::with('usersCreated:name,id,email')
                ->with('usersUpdated:name,id,email')
                ->where('flow_type', 'work_order')
                ->get();
            foreach ($services as $service) {
                $component_id = json_decode($service['component_id'], true);
                $component_name = Component::whereIn('id', $component_id)->pluck('title');
                $service['component_name'] = $component_name;
            }
        }

        return FacilityServiceResource::collection($services);
    }

    public function indexServiceRequest()
    {
        $user = Auth::user();
        $services = FacilityService::where('user_created_id', $user->id)
            ->where('flow_type', 'service_request')
            ->with('usersCreated:name,id,email')
            ->with('usersUpdated:name,id,email')
            ->get();
        foreach ($services as $service) {
            $component_id = json_decode($service['component_id'], true);
            $component_name = Component::whereIn('id', $component_id)->pluck('title');
            $service['component_name'] = $component_name;
        }
        return FacilityServiceResource::collection($services);
    }

    public function indexWorkOrder()
    {
        $user = Auth::user();
        $services = FacilityService::where('user_created_id', $user->id)
            ->where('flow_type', 'Work_order')
            ->with('usersCreated:name,id,email')
            ->get();
        foreach ($services as $service) {
            $component_id = json_decode($service['component_id'], true);
            $component_name = Component::whereIn('id', $component_id)->pluck('title');
            $service['component_name'] = $component_name;
        }
        return FacilityServiceResource::collection($services);
    }

    public function store(Request $request)
    {
        try {
            $latest_service = FacilityService::orderBy('created_at', 'DESC')->first();

            if ($latest_service) {
                $service_number = '#' . str_pad($latest_service->id + 1, 5, "0", STR_PAD_LEFT);
            } else {
                $service_number = '#' . str_pad(1, 5, "0", STR_PAD_LEFT);
            }
            $input = $request->all();
            $input['doc_number'] = $service_number;
            $input['user_email'] = User::where('id',$input['user_created_id'])->pluck('email');
            FacilityService::create($input);
            $serviceId = FacilityService::where('doc_number', $service_number)->pluck('id')->first();
            dispatch(function () use ($input) {
                $this->sendEmailComplain($input);
            })->afterResponse();  
            //dd($serviceId);
            return response()->json(['data' => $serviceId]);          
            //return ItemCollection::collection($item);

        } catch (Exception $ex) {
            return $this->sendError('Execution Error.', $ex->getMessage());
        }



        //
    }

    public function update(Request $request, $id)
    {
        $service = FacilityService::findOrFail($id);
        $input = $request->all();
        //dd($input);
        if (isset($input['item']) && !empty($input['item'])) {
            $serviceItem = ServiceItem::where('facility_service_id', $id)->get();
            //dd($serviceItem);
            if ($serviceItem) {
                foreach ($serviceItem as $item) {
                    $item->delete();
                }
            }
            foreach ($input['item'] as $item) {
                $item['facility_service_id'] = $id;
                ServiceItem::create($item);
            }
        }
        $service->update($input);
        dispatch(function () use ($input) {
            $this->sendEmailComplain($input);
        })->afterResponse();
        return response()->json(['message' => 'Data updated successfully.']);
    }

    public function destroy($id)
    {
        $service = FacilityService::findOrFail($id);
        $service->delete();
        return response()->json(['message' => 'Booking deleted successfully.']);
    }

    public function download(Request $request)
    {
        $importData = AttachmentFile::find($request->id);
        //dd($importData);
        if ($importData) {
            $path = Storage::disk('local')->path($importData->file_path);
            if (Storage::disk('local')->exists($importData->file_path)) {
                $fileName = $importData->file_name;
                return response()->download($path, $fileName);
            } else {
                return response()->json(['error' => 'File not found'], 404);
            }
        }
    }
}
