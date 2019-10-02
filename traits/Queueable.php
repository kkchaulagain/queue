<?php

trait Queueable
{
    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection;

    /**
     * Unique id of job
     * If this id is present the jobs table is not updated
     *
     * @var string|null
     */
    public $jobId;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'default';

    /**
     * The name of the connection the chain should be sent to.
     *
     * @var string|null
     */
    public $chainConnection;

    /**
     * The name of the queue the chain should be sent to.
     *
     * @var string|null
     */
    public $chainQueue;

    /**
     * The number of seconds before the job should be made available.
     *
     * @var \DateTimeInterface|\DateInterval|int|null
     */
    public $delay;

    /**
     * For internal mechanism . don't change this
     *
     */
    public $nosave = false;

    /**
     * Set the desired connection for the job.
     *
     * @param string|null $connection
     * @return $this
     */
    public function onConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function onJobId($id)
    {
        $this->jobId = $id;
        return $this;

    }

    /**
     * Set the desired queue for the job.
     *
     * @param string|null $queue
     * @return $this
     */
    public function onQueue($queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Set the desired connection for the chain.
     *
     * @param string|null $connection
     * @return $this
     */
    public function allOnConnection($connection)
    {
        $this->chainConnection = $connection;
        $this->connection = $connection;

        return $this;
    }

    /**
     * Set the desired queue for the chain.
     *
     * @param string|null $queue
     * @return $this
     */
    public function allOnQueue($queue)
    {
        $this->chainQueue = $queue;
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set the desired delay for the job.
     *
     * @param \DateTimeInterface|\DateInterval|int|null $delay
     * @return $this
     */
    public function delay($delay)
    {
        $this->delay = $delay;

        return $this;
    }

    public function saveToDatabase($payload)
    {
        if ($this->checkForId()) {
            $date = new DateTime();
            $timestamp = $date->getTimestamp();
            Yii::import('application.models.Jobs');
            $model = new Jobs();
            $model->queue = $this->queue;
            $model->payload = json_encode($payload);
            $model->reserved_at = $timestamp;
            $model->created_at = $timestamp;
            if ($this->jobId) {
                $model->job_id = $this->jobId;
            }
            $model->available_at = $this->getAvailableAt($timestamp);
            $model->save();
        }
    }

    private function getAvailableAt($timestamp)
    {
        if ($this->delay) {
            $date = new DateTime();
            $interval = PT . $this->delay . S;
            $date->add(new DateInterval($interval));
            return $date->getTimestamp();
        } else {
            return $timestamp;
        }
    }

    public function getNameOfClass()
    {
        return get_called_class();
    }

    public function setProperties($properties)
    {
        foreach ($properties as $key => $property) {
            $this->$key = $property;
        }
        $this->nosave = true;
    }

    public function checkForId()
    {
        if ($this->jobId && $this->searchForSameJobId() > 0) {
            $this->nosave = true;
            return false;
        }
        return true;
    }

    protected function searchForSameJobId()
    {
        $Criteria = new CDbCriteria();
        $Criteria->condition = "job_id='$this->jobId'";
        $Criteria->limit = 10000;
        return count(Jobs::model()->findAll($Criteria));
    }
}
