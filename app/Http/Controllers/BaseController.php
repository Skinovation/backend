<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Mengembalikan response sukses dengan format standar.
     *
     * @param mixed $data Data yang akan dikirimkan dalam response
     * @param string $message Pesan sukses (default: "Berhasil")
     * @param int $status HTTP status code (default: 200)
     * @return JsonResponse
     */
    public function sendResponse($data, $message = "Berhasil", $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Mengembalikan response error dengan format standar.
     *
     * @param string $message Pesan error yang akan ditampilkan
     * @param array $errors Detail error tambahan (opsional)
     * @param int $status HTTP status code (default: 400)
     * @return JsonResponse
     */
    public function sendError($message, $errors = [], $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
