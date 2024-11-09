<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait ErrorLogger
{
    protected function setLogInfo($class_name, $function_name, $message, $line)
    {
        $request_url = request()->fullUrl();

        Log::info("Error Logger\nAccess URL : " . $request_url . "\nClass Name : " . $class_name . "\nFunction : " . $function_name . "\nMessage : " . $message . "\nLine : " . $line);
    }
}
