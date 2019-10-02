<?php

Yii::import('application.services.Queue.Bus.*');

trait Dispatchable
{
    public static function dispatch()
    {
        $job = new static(...func_get_args());
        $job->checkForId();
        return $job;

    }

    public function __destruct()
    {
        if (!$this->nosave) {
            $payload['data'] = json_encode($this);

            $payload['classname'] = $this->getNameOfClass();
            $this->saveToDatabase($payload);
        }
    }

}
