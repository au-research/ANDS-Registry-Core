<?php

// Sample configuration for the logging library

$config = array(
    'registry' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => 'registry'
    ),
    'importer' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => 'importer'
    ),
    'activity' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => 'activity'
    ),
    'portal' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => 'portal'
    ),
    'error' => array(
        'level' => 'ERROR',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => 'error'
    ),
    'email_criticals' => array(
        'level' => 'CRITICAL',
        'type' => 'email',
        'format' => "{date} - {level}: {message}",
        'to' => '',
        'from' => '',
        'subject' => 'New critical logging message'
    )
);