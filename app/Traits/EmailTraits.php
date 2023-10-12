<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Item;
use App\Mail\MailNotification;
use Carbon\Exceptions\Exception;
use Exception as GlobalException;
use Illuminate\Support\Facades\Log;

trait EmailTraits
{
    // trait code here
    public function sendEmailBooking($input, $type = null)
    {
        try {
            Log::info(__METHOD__ . 'Type =>' . $type);
            $user = User::findOrFail($input['user_id']);
            $item = Item::findOrFail($input['item_id']);
            $dateTimeStart = Carbon::parse($input['start_time']);
            $dateTimeEnd = Carbon::parse($input['end_time']);
            $startDateTime = $dateTimeStart->format('d-m-Y h:i A');
            $endDateTime = $dateTimeEnd->format('d-m-Y h:i A');
            if ($type == 'new_booking') {
                $body =  'Your booking has been created from ' . $startDateTime . ' until ' . $endDateTime;
                $title =  'New ' . $item->name . ' Booking Created';
                $adminBody = 'Booking by ' . $user->name . ' at ' . $startDateTime . ' until ' . $endDateTime;
            }
            if ($type == 'update_booking') {
                Log::info(__METHOD__ . 'Update Booking');
                $body = 'Your booking has been updated from ' . $startDateTime . ' until ' . $endDateTime;
                $title =  $item->name . ' Booking Updated';
                $adminBody = 'Booking by ' . $user->name . ' at ' . $startDateTime . ' until ' . $endDateTime;
            }
            if ($type == 'delete_booking') {
                $body = 'Your booking hse been cancelled. Remarks: ' .  $input['remarks'];
                $title = $item->name . ' Booking Cancelled';
                $adminBody = 'Remarks: ' . $input['remarks'] . ' booking cancelled by ' . $user->name;
            }
            Log::info(__METHOD__ . 'Type =>' . $type);
            $mailData = [
                'title' => $title,
                'body' => $body,
                'end' => 'Please Make Sure Follow All The Facilities Rules',
            ];

            Mail::to($user->email)->send(new MailNotification($mailData));

            $this->adminEmail($title, $adminBody);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Send User Email Error', 'error' => $exception], 500);
        }
    }

    public function sendEmailComplain($input)
    {
        /* $title =' Sucessfully Created';
        $body = 'New Work Order Created With';
        $end = 'Thank you again for your valuable input.';
        $adminBody = 'New Work Order has been submitted';
        //Log::info(__METHOD__ . $input);
        $mailData = [
            'title' => $title,
            'body' => $body,
            'end' => $end,
        ];

        Mail::to('khairulfaisal@commercedc.com.my')->send(new MailNotification($mailData)); */

        //$this->serviceFacility($title, $adminBody);
        try {
            
            if ($input['status'] == 'New') {

                $title = $input['flow_type'] . ' Sucessfully Created';
                if ($input['flow_type'] == 'work_order') {
                    $body = 'New Work Order Created With';
                    $end = 'Thank you again for your valuable input.';
                    $adminBody = 'New Work Order has been submitted';
                } else {
                    $body = 'Thank you for reaching out to us and bringing this matter to our attention. We apologize for any inconvenience you may have experienced';
                    $end = 'Thank you for bringing this matter to our attention.';
                    $adminBody = 'New Service Request has been submitted';
                }
            }


            if ($input['status'] == 'Work In Progress') {
                $title = $input['flow_type'] . ' Updated';
                if ($input['flow_type'] == 'work_order') {
                    $body = 'Work Order Has Been Processed By Our Technician';
                    $end = 'Thank you  for your patience.';
                    $adminBody = 'Work Order Work In Progress';
                } else {
                    $body = 'Service Request Has Been Processed By Our Technician';
                    $end = 'Thank you for your patience';
                    $adminBody = 'Service Request Work In Progress';
                }
            }


            if ($input['status'] == 'Closed') {
                $title = $input['flow_type'] . ' Closed';
                if ($input['flow_type'] == 'work_order') {
                    $body = 'Work Order Has Been Closed By Our Technician';
                    $end = 'Thank you  for your patience.';
                    $adminBody = 'Work Order Closed';
                } else {
                    $body = 'Service Request Has Been Closed By Our Technician';
                    $end = 'Thank you for your patience';
                    $adminBody = 'Service Request Closed';
                }
            }
            

            $mailData = [
                'title' => $title,
                'body' => $body,
                'end' => $end,
            ];

            Mail::to($input['user_email'][0])->send(new MailNotification($mailData));

            $this->serviceFacility($title, $adminBody);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Send User Email Error', 'error' => $exception], 500);
        }
    }

    public function emailReminder($booking)
    {
        try {
            Log::info(__METHOD__ . $booking);
            $startDateTime = Carbon::parse($booking->start_time)->format('d-m-Y h:i A');
            $endDateTime = Carbon::parse($booking->end_time)->format('d-m-Y h:i A');

            $mailData = [
                'title' => 'Booking Reminder',
                'body' => 'Booking for ' . $booking->items->name . ' at ' . $startDateTime . ' until ' . $endDateTime,
                'end' => 'Please Make Sure Follow All The Facilities Rules',

            ];
            Mail::to($booking->users->email)->send(new MailNotification($mailData));
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Send Email Reminder Error', 'error' => $exception], 500);
        }
    }

    public function adminEmail($title, $adminBody)
    {
        try {
            $adminMailData = [
                'title' => $title,
                'body' => $adminBody,
                'end' => 'Plese login for more information'
            ];
            $adminUsers = User::whereIn('role', ['Admin', 'admin-sport'])->get();
            foreach ($adminUsers as $adminUser) {
                Mail::to($adminUser->email)->send(new MailNotification($adminMailData));
            }
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Send Email Admin Error', 'error' => $exception], 500);
        }
    }


    public function serviceFacility($title, $adminBody)
    {
        try {
            $adminMailData = [
                'title' => $title,
                'body' => $adminBody,
                'end' => 'Plese login for more information'
            ];
            $adminUsers = User::whereIn('role', ['admin-technician', 'admin-approver', 'Admin'])->get();
            foreach ($adminUsers as $adminUser) {
                Mail::to($adminUser->email)->send(new MailNotification($adminMailData));
            }
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Send Email Admin Error', 'error' => $exception], 500);
        }
    }
}
