<?php

namespace App\Helpers;

trait ServiceReturner
{
    protected function returnSuccess($data = null)
    {
        return ['status' => true , 'data' => $data];
    }

    protected function returnError($message)
    {
        return ['status' => false , 'message' => $message];
    }
}
