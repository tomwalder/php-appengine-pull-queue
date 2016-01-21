# Pull Task Queues for PHP on Google App Engine #

This library provides native PHP access to the Google App Engine **PULL** Task Queue.

At the time of writing there is no off-the-shelf way to access this from the PHP runtime.

**ALPHA** This library is in the very early stages of development. Do not use it in production. It will change.

## Table of Contents ##

- [Examples](#examples)
- [Install with Composer](#install-with-composer)
- [References](#queries)

## Examples ##

I find examples a great way to decide if I want to even try out a library, so here's a couple for you.

All the examples assume you have set up a pull queue called `pullqueue` in your `queue.yaml` file.

Details here: https://cloud.google.com/appengine/docs/python/config/queue#Python_Defining_pull_queues

### Add One Task ###

```php
// Create a task and give it a payload
$obj_task = new \AEQ\Pull\Task();
$obj_task->setPayload('Some data here');

// Add the task to a named queue
$obj_queue = new \AEQ\Pull\Queue('pullqueue');
$obj_queue->addTask($obj_task);
```

### Lease then Delete a Task ###

```php
// Create the queue
$obj_queue = new \AEQ\Pull\Queue('pullqueue');

// Lease 1 task
foreach($obj_queue->leaseTasks(1) as $obj_task) {
   // Do any work we want to 
   echo $obj_task->getPayload();
   // Delete the task once done
   $obj_queue->deleteTask($obj_task);
}
```

## Install with Composer ##

To install using Composer, use this require line in your `composer.json` for bleeding-edge features, dev-master

`"tomwalder/php-appengine-pull-queue": "dev-master"`

Or, if you're using the command line:

`composer require tomwalder/php-appengine-pull-queue`

You will need `minimum-stability: dev`


## References ##

- [Defining Pull Queues](https://cloud.google.com/appengine/docs/python/config/queue#Python_Defining_pull_queues)
- [Push Queue Docs](https://cloud.google.com/appengine/docs/php/taskqueue/)

