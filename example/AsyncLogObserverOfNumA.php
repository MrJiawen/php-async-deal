<?php

namespace CjwAsync\example;

use CjwAsync\Src\AsyncLogHandle\AsyncLogObserverOfGenerator;

class AsyncLogObserverOfNumA extends AsyncLogObserverOfGenerator
{
    public function onHandle()
    {
       return false;
    }
}