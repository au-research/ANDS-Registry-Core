<?php

$config = array_dot(\ANDS\Util\Config::get('app'));
$path = (array_key_exists('storage.logs.legacy_path', $config)) ? $config['storage.logs.legacy_path'] : null;

$path = $path != "" ? rtrim($path,'/').'/' : "";

$config = array(
    'registry' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => $path . 'registry'
    ),
    'importer' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => $path . 'importer'
    ),
    'activity' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => $path . 'activity'
    ),
    'portal' => array(
        'level' => 'INFO',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => $path . 'portal'
    ),
    'error' => array(
        'level' => 'ERROR',
        'type' => 'file',
        'format' => '[date:{date}] {message}',
        'file_path' => $path . 'error'
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