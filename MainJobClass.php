<?php


Yii::import('application.services.Queue.traits.*');

class MainJobClass
{
    use Dispatchable, Queueable, InteractWithQueue, SerializeModels;
    public $args, $another;

    public function __construct($args=[],$new=[])
    {
        $this->args = $args;
        $this->another = $new;

    }

    public function handle()
    {
        dd($this);
    }

}