<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Notifications\NewUserNotification;
use App\Notifications\NewUserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function index()
    {
        return UserResource::collection(User::all());
    }

    public function userEmail ()
    {
        $emails = User::pluck('email')->toArray();

        return response()->json(['data' => $emails]);
    }

    public function update(Request $request, User $user)
    {
        //
        $user = DB::table('users')->find($user->id);
        //dd($request->role);
        if ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'role' => $request->role,
                    'user_group' => $request->user_group,
                    'is_active' => $request->is_active
                ]);

            return response()->json(['message' => 'User updated successfully.']);
        }
        //$user->update($request->all());
    }
    public function destroy(User $user)
    {
        Log::info(self::class . ' ' . __FUNCTION__ . 'url :  ' . $user);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
        //
    }
    public function store(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'company' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();
        $input['is_registered'] = true;
        $input['password'] = bcrypt($input['password']);
        User::create($input);
        $adminUsers = User::whereIn('role', ['Admin'])->get();
        $userEmail = $input['email'];
        $user = User::where('email', $userEmail)->first();
        //dd($userEmail);
        $user->notify(new NewUserNotification($input));
        foreach ($adminUsers as $adminUser) {
            $adminUser->notify(new NewUserRegistered($input));
        }

        return response()->json(['message' => 'Account Created Successfully']);
    }

}
