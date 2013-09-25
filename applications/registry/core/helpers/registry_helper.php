<?php

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