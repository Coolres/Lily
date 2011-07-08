<?php

abstract class Lily_Queue_Job_Abstract {
    protected $_queue;
    protected $_attempts;
    protected $_max_attempts = 10;
    protected $_id;
    protected $_payload;
    protected $_is_enqueued = false;
    
    /**
     * constructor
     * @param array $options 
     * @param mixed $payload 
     */
    public function __construct($options=array(), $payload=null) {
        if (!isset($options['name'])) {
            throw new Lily_Config_Exception("queue.role.$role.name");
        }
        $this->_queue = Lily_Queue_Manager::getAdapter($options['name']);
        $this->_payload = $payload;
    }
    
    /**
     * Enqueue the job. This used the current
     * queue adapter and pushes the job onto the queue.
     * @return bool $this->_is_enqueued
     */
    public function enqueue() {
        if ($this->_is_enqueued) {
            throw new Lily_Queue_Exception("Job is already enqueued");
        }
        if (!$this->hasAttemptsLeft()) {
            throw new Lily_Queue_Exception("Job exceeded max attempts");
        }
        if (is_null($this->_payload)) {
            throw new Lily_Queue_Exception("Can't enqueue a job with null payload");
        }
        $result = $this->_queue->push($this);
        $this->_is_enqueued = (bool)$result;
        return $this->_is_enqueued;
    }
    
    /**
     * Check if the job has attempts left to be run.
     * @return bool
     */
    public function hasAttemptsLeft() {
        if ($this->_attempts < $this->_max_attempts) {
            return true;
        }
        return false;
    }
    
    /**
     * This is the meat of job. This will be defined per
     * each job and is what it should do when it's pulled off
     * by a worker. 
     * @return bool
     */
    abstract public function perform();
    
    /**
     * Set attempts.
     * @param int $num
     * @return $this
     */
    public function setAttempts($num) {
        $this->_attempts = $num;
        return $this;
    }
    
    /**
     * Get attempts.
     * @return int $this->_max_attempts
     */
     public function getAttempts() {
         return $this->_attempts;
     }
    
    /**
     * Set max attempts.
     * @param int $num
     * @return $this
     */
    public function setMaxAttempts($num) {
        $this->_max_attempts = $num;
        return $this;
    }
    
    /**
     * Get max attempts.
     * @return int $this->_max_attempts
     */
     public function getMaxAttempts() {
         return $this->_max_attempts;
     }
    
    /**
     * Set the unique id for the job.
     * @param int $id
     * @return $this
     */
    public function setId($id) {
        $this->_id = $id;
        return $this;
    }
    
    /**
     * Get then unique id for the job.
     * @return int $this->_id;
     */
    public function getId() {
        return $this->_id;
    }
    
    /**
     * Set the payload that gets saved to the queue.
     * @param mixed $payload 
     * @return $this
     */
    public function setPayload($payload) {
        $this->_payload = $payload;
        return $this;
    }
    
    /**
     * Get the payload.
     * @return mixed $this->_payload
     */
    public function getPayload() {
        return $this->_payload;
    }
    
    /**
     * Set isEnqueued flag.
     * @param mixed $value 
     * @return $this
     */
    public function setIsEnqueued($value) {
        $this->_is_enqueued = (bool)$value;
        return $this;
    }
    
    /**
     * Get isEnqueued flag.
     * @return bool $this->_is_enqueued
     */
    public function getIsEnqueued() {
        return (bool)$this->_is_enqueued;
    }
}