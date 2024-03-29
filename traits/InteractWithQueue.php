<?php


/**
 * Class InteractWithQueue
 */
trait InteractWithQueue
{

    /**
     * The underlying queue job instance.
     *
     * @var
     */
    protected $job;

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->job ? $this->job->attempts() : 1;
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->job) {
            return $this->job->delete();
        }
    }

    /**
     * Fail the job from the queue.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function fail($exception = null)
    {
        if ($this->job) {
            echo 'this job failed';
        }
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        if ($this->job) {
            return $this->job->release($delay);
        }
    }


    /**
     *
     * Set the base queue job instance
     * @param JobContract $job
     * @return $this
     */
    public function setJob(JobContract $job)
    {
        $this->job = $job;

        return $this;
    }


}


