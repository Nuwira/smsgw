<?php
namespace Nuwira\Smsgw;
use Illuminate\Support\Facades\Facade;

class SmsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'nuwira-sms';
    }
}