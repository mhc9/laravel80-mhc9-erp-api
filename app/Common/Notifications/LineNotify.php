<?php

namespace App\Common\Notifications;

use App\Interfaces\INotify;
use Phattarachai\LineNotify\Facade\Line;

class LineNotify implements INotify
{
    public function send(string $message)
    {
        Line::send($message);
    }
}