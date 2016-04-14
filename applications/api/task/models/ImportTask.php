<?php
/**
 * Class:  ImportTask
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Task;

use \Exception as Exception;

/**
 * Class ImportTask
 * Import a payload into the registry
 * Contains a pipeline that houses several ImportSubTask
 * That can run in sequences to provide better control over the data
 * and improve synchronisity
 *  *
 * @package ANDS\API\Task
 */
class ImportTask extends Task
{
    //pipeline is part of message returned
    private $pipeline = [];
    private $affectedRecords = [];

    public function run_task()
    {

        $this->loadParams();
        $this->log('Import Task started');

        if (array_key_exists('task', $_GET)) {
            $task = $this->getSpecificTask($_GET['task']);
            if (!$task) {
                throw new Exception("Undefined task: " . $_GET['task']);
            }
        } else {
            $task = $this->nextSubTask();
        }

        if ($task) {
            $this->log('Running ' . $task->getName() . ' Import subtask');
            if (sizeof($this->getAffectedRecords()) > 0) {
                $task->setAffectedRecords($this->getAffectedRecords());
            }
            try {
                $task->run();
            } catch (Exception $e) {
                $task->stoppedWithError($e->getMessage());
                throw new Exception($e->getMessage());
            } catch (NonFatalException $e) {
                $task->addError($e->getMessage());
            }

            if ($ids = $task->getAffectedRecords()) {
                if (sizeof($ids) > 0) {
                    $this->setAffectedRecords($ids);
                }
            }
        } else {
            $this->log('No Task found');
        }
    }

    /**
     * @Override
     * If there are still task left to do
     * Set the parent task (this task) back to PENDING
     * to be available to test again
     */
    public function hook_end()
    {
        $task = $this->nextSubTask();
        if ($task) {
            $this->setStatus('PENDING')->save();
        }
        $this->save();
    }

    /**
     * Returns a specific task by name
     *
     * @param $name
     * @return Task
     */
    private function getSpecificTask($name)
    {
        foreach ($this->pipeline as $task) {
            if ($task->getName() == $name) {
                return $task;
            }
        }
        return null;
    }

    /**
     * Return the next task in the pipeline
     * status=PENDING
     *
     * @return Task
     */
    public function nextSubTask()
    {
        foreach ($this->pipeline as $task) {
            if ($task->getStatus() == 'PENDING') {
                return $task;
            }
        }
        return null;
    }



    public function loadParams()
    {
        if (array_key_exists('pipeline', $this->getMessage())) {
            $this->setPipeline($this->message['pipeline']);
        } else {
            $this->setPipeline($this->defaultImportPipeline());
        }
        foreach ($this->pipeline as &$process) {
            if (array_key_exists('name', $process)) {
                $className = 'ANDS\\API\\Task\\ImportSubTask\\' . $process['name'];
                if (class_exists($className)) {
                    $processObject = new $className();
                    $processObject->init($process);
                    $process = $processObject;
                } else {
                    throw new Exception('Class not found: ' . $className);
                }
            }
        }
        $this->setPipeline($this->pipeline);

        if (array_key_exists('affected_records', $this->getMessage())) {
            $this->setAffectedRecords($this->message['affected_records']);
        } else {
            $this->setAffectedRecords([]);
        }
    }

    /**
     * @return mixed
     */
    public function getPipeline()
    {
        return $this->pipeline;
    }

    /**
     * @param $pipeline
     */
    public function setPipeline($pipeline)
    {
        $this->pipeline = $pipeline;
        $this->message['pipeline'] = $this->pipeline;
    }

    /**
     * Add a Task to the pipeline
     * Currently unused but could be very useful later on
     *
     * @param $task
     */
    public function addPipeline($task)
    {
        array_push($this->pipeline, $task);
        $this->message['pipeline'] = $this->pipeline;
    }

    /**
     * @return array
     */
    public function getAffectedRecords()
    {
        return $this->affectedRecords;
    }

    /**
     * @param  $affectedRecords
     */
    public function setAffectedRecords($affectedRecords)
    {

        if(is_array($affectedRecords))
        {
            foreach($affectedRecords as $record_key)
            {
                if(!in_array($record_key, $this->affectedRecords)){
                    array_push($this->affectedRecords, $record_key);
                }
            }
        }
        else{
            if(!in_array($affectedRecords, $this->affectedRecords)){
                array_push($this->affectedRecords, $affectedRecords);
            }
        }

        $this->message['affected_records'] = $this->affectedRecords;

    }

    /**
     * The default import pipeline that applies to any
     * given import payload statement
     * Regularly used by the harvesting workflow
     *
     * @return array
     */
    private function defaultImportPipeline()
    {
        return [
            [
                'name' => 'Insert',
                'status' => 'PENDING',
                'priority' => 1,
                'params' => $this->getParams()
            ],
            [
                'name' => 'ExtractIdentifiers',
                'status' => 'PENDING',
                'priority' => 2,
                'params' => $this->getParams()
            ],
            [
                'name' => 'ExtractRelationships',
                'status' => 'PENDING',
                'priority' => 2,
                'params' => $this->getParams()
            ],
            [
                'name' => 'ProcessRelationships',
                'status' => 'PENDING',
                'priority' => 2,
                'params' => $this->getParams()
            ],
            [
                'name' => 'ProcessQuality',
                'status' => 'PENDING',
                'priority' => 2,
                'params' => $this->getParams()
            ],
            [
                'name' => 'SolrIndexRelations',
                'status' => 'PENDING',
                'priority' => 2,
                'params' => $this->getParams()
            ],
            [
                'name' => 'SolrIndexPortal',
                'status' => 'PENDING',
                'priority' => 2,
                'params' => $this->getParams()
            ]
        ];
    }

}