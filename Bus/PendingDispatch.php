<?php

Yii::import('application.services.Queue.Contracts.*');

class PendingDispatch
{

    /**
     * The job.
     *
     * @var mixed
     */
    public $job, $payload;

    /**
     * Create a new pending job dispatch.
     *
     * @param mixed $job
     * @return void
     */
    public function __construct($job)
    {
        $payload = [];
        $this->job = $job;
        $payload['data'] = json_encode($this->job);

        $payload['classname'] = $this->job->getNameOfClass();
        $this->payload = $payload;
//        $this->job->saveToDatabase($payload);

    }

    public function savetoDb()
    {

    }

    public function __destruct()
    {
        dd($this->job->queue);
        $this->job->saveToDatabase($this->payload);
    }

}