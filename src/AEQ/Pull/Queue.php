<?php namespace AEQ\Pull;
/**
 * Copyright 2016 Tom Walder
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
use google\appengine\runtime\ApiProxy;
use google\appengine\runtime\ApplicationError;
use google\appengine\TaskQueueBulkAddRequest;
use google\appengine\TaskQueueBulkAddResponse;
use google\appengine\TaskQueueDeleteRequest;
use google\appengine\TaskQueueDeleteResponse;
use google\appengine\TaskQueueQueryAndOwnTasksRequest;
use google\appengine\TaskQueueQueryAndOwnTasksResponse;
use google\appengine\TaskQueueServiceError\ErrorCode;

class Queue
{

    /**
     * Name of the queue
     *
     * @var string
     */
    protected $str_name = 'pullqueue';

    /**
     * Set the queue name
     *
     * @param string $str_name
     */
    public function __construct($str_name = 'pullqueue')
    {
        $this->str_name = $str_name;
    }

    /**
     * Get the Queue name
     *
     * @return string
     */
    public function getName()
    {
        return $this->str_name;
    }

    /**
     * Add a single task
     *
     * @param Task $obj_task
     * @return array
     */
    public function addTask(Task $obj_task)
    {
        return $this->addTasks([$obj_task]);
    }

    /**
     * Add many tasks
     *
     * @param Task[] $arr_tasks
     * @return array
     */
    public function addTasks(array $arr_tasks)
    {

        // Set up request and response objects
        $obj_request = new TaskQueueBulkAddRequest();
        $obj_response = new TaskQueueBulkAddResponse();

        // Add the tasks
        $int_now = time();
        $int_now_usec = $int_now * 1e6;
        foreach ($arr_tasks as $obj_task) {
            $obj_add_request = $obj_request->addAddRequest();
            $obj_add_request
                ->setQueueName($this->str_name)
                ->setMode(\google\appengine\TaskQueueMode\Mode::PULL)
                ->setEtaUsec($int_now_usec)
                ->setTaskName($obj_task->getName())
                ->setBody($obj_task->getPayload());
        }

        // Make the request
        $this->makeCall('BulkAdd', $obj_request, $obj_response);

        // Extract task names
        $arr_task_names = [];
        $arr_results = $obj_response->getTaskResultList();
        foreach ($arr_results as $index => $task_result) {
            if ($task_result->hasChosenTaskName()) {
                $arr_task_names[$index] = $task_result->getChosenTaskName();
            }
            if ($task_result->getResult() != ErrorCode::OK) {
                throw new \RuntimeException($this->translateResultCode($task_result->getResult()));
            }
        }
        return $arr_task_names;

    }

    /**
     * Lease one or more tasks
     *
     * @param int $int_tasks
     * @param int $int_lease_duration
     * @return array
     */
    public function leaseTasks($int_tasks = 1, $int_lease_duration = 30)
    {
        // Set up the request, response pair
        $obj_request = new TaskQueueQueryAndOwnTasksRequest();
        $obj_response = new TaskQueueQueryAndOwnTasksResponse();

        // Configure the request
        $obj_request
            ->setQueueName($this->str_name)
            ->setMaxTasks($int_tasks)
            ->setLeaseSeconds($int_lease_duration);

        // Make the call
        $this->makeCall('QueryAndOwnTasks', $obj_request, $obj_response);

        // Process the response into Tasks
        $arr_tasks = [];
        foreach($obj_response->getTaskList() as $obj_source_task) {
            $obj_task = new Task();
            $obj_task->setName($obj_source_task->getTaskName());
            $obj_task->setPayload($obj_source_task->getBody());
            $arr_tasks[] = $obj_task;
        }
        return $arr_tasks;
    }

    /**
     * Delete a task
     *
     * @param Task $obj_task
     * @return array
     */
    public function deleteTask(Task $obj_task)
    {
        return $this->deleteTasks([$obj_task]);
    }

    /**
     * Delete an array of one or more tasks
     *
     * @param array $arr_tasks
     * @return array
     */
    public function deleteTasks(array $arr_tasks)
    {
        $obj_request = new TaskQueueDeleteRequest();
        $obj_response = new TaskQueueDeleteResponse();
        $obj_request->setQueueName($this->str_name);
        foreach($arr_tasks as $obj_task) {
            $obj_request->addTaskName($obj_task->getName());
        }
        $this->makeCall('Delete', $obj_request, $obj_response);
        foreach($obj_response->getResultList() as $int_idx => $int_code) {
            if($int_code != ErrorCode::OK) {
                throw new \RuntimeException($this->translateResultCode($int_code));
            }
        }
        return $obj_response->getResultSize();
    }

    /**
     * Make a service call
     *
     * @param $str_call
     * @param $obj_request
     * @param $obj_response
     */
    private function makeCall($str_call, $obj_request, $obj_response)
    {
        try {
            ApiProxy::makeSyncCall('taskqueue', $str_call, $obj_request, $obj_response);
        } catch (ApplicationError $e) {
            throw new \RuntimeException("Failed to [{$str_call}] with: " . $e->getApplicationError());
        }
    }

    /**
     * Translate a return code
     *
     * @param $int_code
     * @return string
     */
    protected function translateResultCode($int_code)
    {
        $arr_codes = [
            0 => 'OK',
            1 => 'UNKNOWN_QUEUE',
            2 => 'TRANSIENT_ERROR',
            3 => 'INTERNAL_ERROR',
            4 => 'TASK_TOO_LARGE',
            5 => 'INVALID_TASK_NAME',
            6 => 'INVALID_QUEUE_NAME',
            7 => 'INVALID_URL',
            8 => 'INVALID_QUEUE_RATE',
            9 => 'PERMISSION_DENIED',
            10 => 'TASK_ALREADY_EXISTS',
            11 => 'TOMBSTONED_TASK',
            12 => 'INVALID_ETA',
            13 => 'INVALID_REQUEST',
            14 => 'UNKNOWN_TASK',
            15 => 'TOMBSTONED_QUEUE',
            16 => 'DUPLICATE_TASK_NAME',
            17 => 'SKIPPED',
            18 => 'TOO_MANY_TASKS',
            19 => 'INVALID_PAYLOAD',
            20 => 'INVALID_RETRY_PARAMETERS',
            21 => 'INVALID_QUEUE_MODE',
            22 => 'ACL_LOOKUP_ERROR',
            23 => 'TRANSACTIONAL_REQUEST_TOO_LARGE',
            24 => 'INCORRECT_CREATOR_NAME',
            25 => 'TASK_LEASE_EXPIRED',
            26 => 'QUEUE_PAUSED',
            27 => 'INVALID_TAG',
            1000 => 'DATASTORE_ERROR'
        ];
        return (isset($arr_codes[$int_code]) ? $arr_codes[$int_code] : 'UNKNOWN');
    }

}