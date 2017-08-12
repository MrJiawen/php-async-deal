<?php

namespace CjwAsync\example;

use CjwAsync\Src\AsyncLogHandle\AsyncLogSubject;
use Illuminate\Console\Command;

class AsyncLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AsyncLog:example';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '通过定时任务，借助日志来完成订阅和发布的功能';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $asyncLogSubject = new AsyncLogSubject();
        $asyncLogSubject->addObserver(new AsyncLogObserverOfNumA());
        $asyncLogSubject->addObserver(new AsyncLogObserverOfNumB());
        $asyncLogSubject->handle();
//        dd($asyncLogSubject);
    }

}
