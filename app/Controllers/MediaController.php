<?php

namespace App\Controllers;

class MediaController extends BaseController
{
    public function show(int $id)
    {
        return "MEDIA ENDPOINT OK - ID: " . $id;
    }
}
