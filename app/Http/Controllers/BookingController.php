<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Resources\BookingResource;
use Illuminate\Support\Facades\Auth;
use App\Traits\EmailTraits;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    use EmailTraits;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin()
    {
        $user = Auth::user();
        if ($user->role == 'Admin' || $user->role == 'admin-sport' ) {
            $bookings = Booking::with('users:name,id,email,user_group', 'items:name,id,maximum_time,limit_user')->withTrashed()->get();
        } 

        return BookingResource::collection($bookings);
    }

    public function indexUser()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)->with('users:name,id,email', 'items:name,id,maximum_time,limit_user')->get();
        return BookingResource::collection($bookings);
    }

    public function findBooking()
    {
        $bookings = Booking::with('users:name,id,email', 'items:name,id,maximum_time,limit_user')->get();
        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'item_id' => 'required',
            'status' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();
        Booking::create($input);
        $type = 'new_booking';
        $this->sendEmailBooking($input, $type);
        return response()->json(['message' => 'data succesfully created']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info(__METHOD__ . 'Update Booking Controller');
            $booking = Booking::findOrFail($id);
            $input = $request->all();
            $booking->update($input);
            $type = 'update_booking';
            $this->sendEmailBooking($input, $type);
            return response()->json(['message' => 'Booking updated successfully.']);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Update Booking Error', 'error' => $exception], 500);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $booking = Booking::findOrFail($id);
        $input = $request->all();
        $booking->update($input);
        $booking->delete();
        $type = 'delete_booking';
        $this->sendEmailBooking($input, $type);
        return response()->json(['message' => 'Booking deleted successfully.']);
    }


    
}
