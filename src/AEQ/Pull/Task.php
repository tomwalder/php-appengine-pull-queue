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
class Task
{

    /**
     * Task name
     *
     * @var string
     */
    protected $str_name = '';

    /**
     * Task payload
     *
     * @var string
     */
    protected $str_payload = '';

    /**
     * ETA, seconds since epoch
     *
     * @var integer
     */
    protected $int_eta = null;
    protected $times_leased = null;

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->str_name;
    }

    /**
     * Get the payload
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->str_payload;
    }

    /**
     * Get the task ETA
     *
     * @return int
     */
    public function getEta()
    {
        return $this->int_eta;
    }

    /**
     * Get the times that this task has been leased
     *
     * @return int
     */
    public function getTimesLeased()
    {
        return $this->times_leased;
    }

    /**
     * Set the name. Fluent.
     *
     * @param $str_name
     * @return $this
     */
    public function setName($str_name)
    {
        $this->str_name = $str_name;
        return $this;
    }

    /**
     * Set the payload. Fluent.
     *
     * @param $str_payload
     * @return $this
     */
    public function setPayload($str_payload)
    {
        $this->str_payload = $str_payload;
        return $this;
    }

    /**
     * Set the task ETA
     *
     * @param $int_eta
     * @return $this
     */
    public function setEta($int_eta)
    {
        $this->int_eta = $int_eta;
        return $this;
    }

    /**
     * Set the times that this task has been leased
     *
     * @return $this
     */
    public function setTimesLeased($times_leased)
    {
        $this->times_leased = $times_leased;
        return $this;
    }
}