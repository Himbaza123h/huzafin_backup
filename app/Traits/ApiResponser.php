<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser{

    protected function successResponse($data, $message = null, $code = 200)
	{
		return response()->json([
			'status'=> 'Success',
            'status_code'=> $code,
			'message' => $message,
			'data' => $data
		], $code);
	}

	protected function errorResponse($errors=null, $message = null,  $code)
	{
		return response()->json([
			'status'=>'Error',
            'status_code'=> $code,
			'message' => $message,
			'errors' => $errors
		], $code);
	}

	protected function callBackSuccessResponse($data, $message = null, $code = 200)
	{
		return response()->json([
            'status'=> $code,
			'message' => $message,
			'data' => $data
		], $code);
	}

	protected function callBackErrorResponse($errors=null, $message = null,  $code)
	{
		return response()->json([
            'status'=> $code,
			'message' => $message,
			'errors' => $errors
		], $code);
	}

}
