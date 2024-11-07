<?php

namespace Warfee\ServiceFilesGenerator\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait ErrorLogger
{
    protected function setLogInfo($class_name, $function_name, $message, $line)
    {
        $request_url = request()->fullUrl();

        Log::info("Error Logger\n
                Access URL : " . $request_url . "\n
                Class Name : " . $class_name . "\n
                Function : " . $function_name . "\n
                Message : " . $message . "\n
                Line : " . $line);
    }
}
