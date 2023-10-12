<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Config;
use App\Models\CompanyLookup;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        try {
            if (strpos($validated['username'], '@') !== false) {
                Log::info(__METHOD__ . 'Masuk');
                if (Auth::guard('web')->attempt(['email' => $request->username, 'password' => $request->password])) {
                    $user = Auth::guard('web')->user();
                    //dd($user);
                    if ($user->is_active == true) {
                        $result = [
                            'user_id' => $user->id,
                            'token' => $user->createToken($validated['username'])->plainTextToken,
                            'name' => $user->name,
                            'email' => $user->email,
                            'company' => $user->company,
                            'role' => $user->role,
                            'id' => $user->id,
                            'group' => $user->user_group,
                            'message' => 'Successfully logged in, Welcome ' . $user->name
                        ];

                        return response()->json($result, 200);
                    } else {
                        throw new AuthenticationException('Your Account Has Been Disabled');
                    }
                } else {
                    throw new AuthenticationException('Invalid username or password.');
                }
            } else {
                if (Auth::guard('ldap')->attempt(['samaccountname' => $validated['username'], 'password' => $validated['password']])) {
                    $user = Auth::guard('ldap')->user();

                    $user_group = CompanyLookup::where('company_name', $user->company)->pluck('company_group')->first();
                    if ($user_group) {
                        $user->user_group = $user_group;
                        $user->save();
                    }
                    $result = [
                        'user_id' => $user->id,
                        'token' => $user->createToken($validated['username'])->plainTextToken,
                        'name' => $user->name,
                        'email' => $user->email,
                        'company' => $user->company,
                        'role' => $user->role,
                        'id' => $user->id,
                        'group' => $user->user_group,
                        'message' => 'Successfully logged in, Welcome ' . $user->name
                    ];

                    return response()->json($result, 200);
                } else {
                    throw new AuthenticationException('Invalid username or password.');
                }
            }
        } catch (AuthenticationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 401);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $token = $request->user()->currentAccessToken();
            if ($token && !$token instanceof \Laravel\Sanctum\TransientToken) {
                $token->delete();
            }
            return response()->json('logout success', 200);
        } else {
            return response()->json('Unauthenticated', 401);
        }
    }
}
