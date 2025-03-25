<?php

namespace App\Interfaces;

interface INotify
{
    public function send(string $message);
}