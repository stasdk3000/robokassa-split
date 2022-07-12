<?php

declare(strict_types=1);

namespace Rbsp;

class Plugin
{
    public function __construct()
    {

        new Options();
        new Endpoint();
    }
}