<?php

namespace App\Common;

use App\Interfaces\INotify;
use Phattarachai\LineNotify\Facade\Line;

class LineNotify implements INotify
{
    public function send($message)
    {
        Line::send($lineMsg);
    }
}