<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * return error response.
     *
     * 400 Bad Request
     * 401 Unauthorized
     * 403 Forbidden
     * 405 Method Not Allowed
     * 406 Not Acceptable
     * 412 Precondition Failed
     * 417 Expectation Failed
     * 422 Unprocessable Entity
     * 424 Failed Dependency
     * 
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404, $request = null)
    {
        $response = [
            'success' => false,
            'request' => $request,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
}
