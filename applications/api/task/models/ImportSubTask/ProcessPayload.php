<?php

namespace ANDS\API\Task\ImportSubTask;


class ProcessPayload extends ImportSubTask
{
    public function run_task()
    {
        /**
         * foreach record in the payload
         * check for records reharvestability
         * if already exists and new version -> ingestable
         * if already exists and not new -> removed and log
         *
         */
    }
}