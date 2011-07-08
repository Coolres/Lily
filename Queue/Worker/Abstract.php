<?php

abstract class Lily_Queue_Worker_Abstract {
    protected $_queue;
    protected $_sleep_duration = 3;
    protected $_stats;
    
    /**
     * Constructor
     * @param array $options
     */
    public function __construct($options) {
        if (isset($options['sleep_duration'])) {
            $this->_sleep_duration = $options['sleep_duration'];
        }
        if (isset($options['name'])) {
            $this->_queue = Lily_Queue_Manager::getAdapter($options['name']);
        } else {
            throw new Lily_Config_Exception("queue.role.$role.name");
        }
        $this->_stats = new Lily_Queue_Worker_Stats();
    }
    
    /**
     * Start the worker on it's infinite journey.
     * Through the space time continuum.
     * @return array
     */
    public function run() {
        $queue_name = $this->_queue->getQueueName();
        $this->_stats->setStartTime();
        
        while (true) {
            $this->_stats->incLoops();
            $job = $this->_queue->pop();
            
            if (is_null($job)) {
                Lily_Log::write('tadpole', "[$queue_name] No items in queue, sleeping...");
                $this->_stats->incSleeps();
                sleep($this->_sleep_duration);
                continue;
            }
            
            $id = $job->getId();
            
            if (!$job->hasAttemptsLeft()) {
                Lily_Log::write('tadpole', "[$queue_name] Job (#{$id}) has exceeded max_attempts.");
                $this->_stats->incSleeps();
                continue;
            }
            
            try {
                if ($job->perform()) {
                    $this->_stats->incJobsCompleted();
                    Lily_Log::write('tadpole', "[$queue_name] Job (#{$id}) completed.");
                } else {
                    // Put it back on the queue
                    $job->enqueue();
                    $this->_stats->incJobsFailed();
                    Lily_Log::write('tadpole', "[$queue_name] Job (#{$id}) failed, re-enqueueing.");
                }
            } catch (Exception $e) {
                $job->enqueue();
                $this->_stats->incJobsFailed();
                Lily_Log::write('tadpole', "[$queue_name] Worker failed with error: " . $e->getMessage());
            }
        }
        
        $this->_stats->setEndTime();
        return $this->_stats->__toArray();
    }
}