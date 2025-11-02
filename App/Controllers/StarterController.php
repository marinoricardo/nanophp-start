<?php

namespace App\Controllers;

use Core\ControllerBase;

class StarterController extends ControllerBase
{
    public function index(): array
    {
        return $this->info("NanoPHP running Successfully", 200);
    }
}