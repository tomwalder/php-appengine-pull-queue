<?php
/**
 * Copyright 2015 Tom Walder
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

/**
 * Tests for Task class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class TaskTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Can we create a Task?
     */
    public function testExists()
    {
        $str_name = 'test-name';
        $str_payload = 'test-payload';
        $obj_task = new \AEQ\Pull\Task();
        $obj_fluent1 = $obj_task->setName($str_name);
        $obj_fluent2 = $obj_task->setPayload($str_payload);

        $this->assertSame($obj_task, $obj_fluent1);
        $this->assertSame($obj_task, $obj_fluent2);
        $this->assertEquals($str_name, $obj_task->getName());
        $this->assertEquals($str_payload, $obj_task->getPayload());
    }
}