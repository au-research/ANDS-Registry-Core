<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 30/03/16
 * Time: 3:34 PM
 */
function getDraftStatusGroup()
{
    return array(MORE_WORK_REQUIRED, DRAFT, SUBMITTED_FOR_ASSESSMENT, ASSESSMENT_IN_PROGRESS, APPROVED);
}

function isDraftStatus($status)
{
    return in_array($status, getDraftStatusGroup());
}

function getPublishedStatusGroup()
{
    return array(PUBLISHED);
}

function isPublishedStatus($status)
{
    return in_array($status, getPublishedStatusGroup());
}