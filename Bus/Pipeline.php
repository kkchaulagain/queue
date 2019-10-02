<?php

Yii::import('application.services.Queue.*');

Yii::import('application.models.Jobs');

class Pipeline
{

    protected $method = 'handle';
    public $data, $classname, $type;
    public $job;
    private $maxAttempts;

    public function __construct($type)
    {
        $this->type = $type;
        $this->setMaxAttempts();

    }

    private function setMaxAttempts()
    {
        try {
            $this->maxAttempts = JOB_MAX_ATTEMPTS;
        } catch (Exception $e) {
            $this->maxAttempts = 5;
        }
    }

    public function executeAllJobs($limit)
    {

        $this->printNowTime();

        $jobs = $this->getJobs($limit);
        if (count($jobs) > 0) {
            $this->exceuteJobs($jobs);
        } else {
            echo "No More Jobs Remaining" . PHP_EOL;
        }
        $this->printNowTime('End');
    }

    public function exceuteJobs($alljobs)
    {
        foreach ($alljobs as $job) {
            $this->data = $job;
            try {
                $this->handleRequest();
            } catch (Exception $e) {
                $this->increaseAttempt($job, $e->getMessage());
                continue;
            }
            $this->deleteJob($job);
        }

    }

    private function printNowTime($type = 'Start')
    {
        echo $type . ' ' . Date('Y-m-d H:i:s') . PHP_EOL;
    }

    public function handleRequest()
    {
        $data = json_decode($this->data['payload']);

        $this->classname = $classname = $data->classname;
        if ($classname) {
            echo $classname . '::Processing' . PHP_EOL;
            $this->initClass($classname, $data->data);
            $this->callHandleMethod();
            echo $classname . '::Completed' . PHP_EOL . PHP_EOL;
        }

    }

    public function initClass($classname, $params)
    {
        $queue = new $classname();
        $properties = json_decode($params);
        $queue->setProperties($properties);
        $this->job = $queue;
    }

    public function callHandleMethod()
    {
        $method = $this->method;
        $this->job->$method();
    }

    protected function getJobs($limit)
    {
        $Criteria = new CDbCriteria();
        $date = new DateTime();
        $timestamp = $date->getTimestamp();

        $Criteria->condition = "queue='$this->type' AND available_at <='$timestamp'";
        $Criteria->limit = $limit;
        return Jobs::model()->findAll($Criteria);
    }

    private function deleteJob(Jobs $jobs, $status = true)
    {
        $this->archiveJob($jobs, $status);
        $jobs->delete();
    }

    private function increaseAttempt(Jobs $job, $message = '')
    {
        $attempts = $job->attempts;
        $job->message = $message;
        if ($attempts < $this->maxAttempts) {
            $job->attempts = $attempts + 1;
            $job->save();
            echo $this->classname . '::failed' . PHP_EOL . PHP_EOL;
        } else {
            $this->deleteJob($job, false);
            echo $this->classname . '::Max Attempt Done. Deleting Queue' . PHP_EOL . PHP_EOL;
        }

    }

    private function archiveJob(Jobs $job, $status = true)
    {
        $jobarchive = new JobsArchive();
        $jobarchive->queue = $job->queue;
        $jobarchive->payload = $job->payload;
        $jobarchive->attempts = $job->attempts;
        $jobarchive->reserved_at = $job->reserved_at;
        $jobarchive->message = $job->message;
        $jobarchive->available_at = $job->available_at;
        $jobarchive->created_at = $job->created_at;
        $jobarchive->job_id = $job->job_id;
        $jobarchive->status = $status;
        $jobarchive->save();
    }
}
