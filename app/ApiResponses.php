<?php

namespace App;

trait ApiResponses
{
    public function successResponse($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function errorResponse($message = 'Error', $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    public function notFoundResponse($message = 'Not Found', $status = 404)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    public function unauthorizedResponse($message = 'Unauthorized', $status = 401)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    public function validationErrorResponse($errors, $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $errors,
        ], $status);
    }

    public function paginateResponse($data, $message = 'Success', $status = 200, $resource = null) {
        // If resource is provided, use it to format the data, otherwise use the data directly
        $formattedData = $resource ? $resource::collection($data->items()) : $data->items();
        $pagination = [
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
        ];
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $formattedData,
            'pagination' => $pagination,
        ], $status);
    }
}
