<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function sendResponse($result, $message, $data = null, $errorMessages = [], $code = 404)
    {
    	$response = [
            'result'  => $result,
            'message' => $message,
        ];
        if($data != null){
            $response['data'] = $data;
        }
        if(!empty($errorMessages)){
            $response['errors'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}
