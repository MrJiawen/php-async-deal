<?php

namespace CjwAsync\example;

use CjwAsync\Src\AsyncLogHandle\AsyncLogObserverOfGenerator;

class AsyncLogObserverOfNumB extends AsyncLogObserverOfGenerator
{
    public function onHandle()
    {
       return true;
    }
}