<?php

class Lily_Queue_Worker_Stats {
    protected $_jobs_completed = 0;
    protected $_jobs_failed = 0;
    protected $_loops = 0;
    protected $_sleeps = 0;
    protected $_start_time = 0;
    protected $_end_time = 0;
    
    /**
     * __toArray
     * @return array
     */
    public function __toArray() {
        return array(
            'jobs_completed' => $this->_jobs_completed,
            'jobs_failed' => $this->_jobs_failed,
            'loops' => $this->_loops,
            'sleeps' => $this->_sleeps,
            'start_time' => $this->_start_time,
            'end_time' => $this->_end_time
        );
    }
    
    /**
     * Increment jobs completed.
     * @return string
     */
    public function incJobsCompleted() {
        return ++$this->_jobs_completed;
    }
    
    /**
     * Get jobs completed.
     * @return string
     */
    public function getJobsCompleted() {
        return $this->_jobs_completed;
    }
    
    /**
     * Increment jobs failed.
     * @return string
     */
    public function incJobsFailed() {
        return ++$this->_jobs_failed;
    }
    
    /**
     * Get jobs failed.
     * @return string
     */
    public function getJobsFailed() {
        return $this->_jobs_failed;
    }
    
    /**
     * Increment loops.
     * @return string
     */
    public function incLoops() {
        return ++$this->_loops;
    }
    
    /**
     * Get loops.
     * @return string
     */
    public function getLoops() {
        return $this->_loops;
    }
    
    /**
     * Increment sleeps.
     * @return string
     */
    public function incSleeps() {
        return ++$this->_sleeps;
    }
    
    /**
     * Get sleeps.
     * @return string
     */
    public function getSleeps() {
        return $this->_sleeps;
    }
    
    /**
     * Set the start time (time()).
     * @return string
     */
    public function setStartTime() {
        $this->_start_time = time();
        return $this;
    }
    
    /**
     * Get the start time.
     * @return string
     */
    public function getStartTime() {
        return $this->_start_time;
    }
    
    /**
     * Set the end time.
     * @return string
     */
    public function setEndTime() {
        $this->_end_time = time();
        return $this;
    }
    
    /**
     * Get end time.
     * @return string
     */
    public function getEndTime() {
        return $this->_end_time;
    }
    
    /**
     * Get time elapsed.
     * @return string
     */
    public function getElapsedTime() {
        return round($this->_end_time - $this->_start_time, 5);
    }
}