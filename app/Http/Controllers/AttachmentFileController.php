<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttachmentFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentFileController extends Controller
{
    //
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

    public function destroy($id)
    {
        $file = AttachmentFile::findOrFail($id);
        if ($file) {
            Storage::disk('local')->delete($file->file_path, $file->file_name);
            $file->delete();
            $listFile = AttachmentFile::where('facility_services_id', $file->facility_services_id )->get();
            return response()->json(['data' => $listFile]);
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $files = $request->file('file_input');
        if (!empty($files)) {
            $facility_services_id = $request->facility_services_id;
            //dd($service);
            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $fileAttachment['facility_services_id'] = $facility_services_id;
                $fileAttachment['file_name'] = $originalName;
                $fileAttachment['file_path'] = $file->store('upload');

                Log::debug('Original Name: ' . $file->store('upload'));
                Log::debug('File Path: ' . $originalName);

                //Log::info(__METHOD__ . $fileAttachment);
                //dd($file);
                AttachmentFile::create($fileAttachment);
                $listFile = AttachmentFile::where('facility_services_id',$facility_services_id)->get();
                //dd($listFile);
            }
        }
        return response()->json(['data' => $listFile]);
    }
}
