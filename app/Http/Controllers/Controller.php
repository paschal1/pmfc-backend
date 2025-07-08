<?php

// App\Http\Controllers\Controller.php
namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

   protected function respondWithData($data)
{
    // Avoid wrapping paginator
    if ($data instanceof \Illuminate\Pagination\AbstractPaginator) {
        return response()->json($data);
    }

    return response()->json([
        'data' => $data
    ]);
}

}
