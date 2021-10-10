<?php

$pass = 0;
$fail = 0;
$assertions = 0;
$failedTest = array();
$testNames = array();
foreach ($results as $result) {
    if ($result['Result'] == 'Passed') {
        $pass++;
    } elseif ($result['Result'] == 'Failed') {
        $fail++;
        $failedTest[] = $result;
    }
    if (!in_array($result['Test Name'], $testNames)) {
        $testNames[] = $result['Test Name'];
    }
    $assertions++;
}
$testCount = sizeof($testNames);

$testSuite = new SimpleXMLElement("<testsuite></testsuite>");
$testSuite->addAttribute('failures', $fail);
$testSuite->addAttribute('tests', $testCount);
$testSuite->addAttribute('time', $elapsed);
$testSuite->addAttribute('name', $testSuiteName);

foreach ($results as $result) {
    $test = $testSuite->addChild('testcase');
    $test->addAttribute('name', $result['Test Name']);
    $test->addAttribute('time', $result['Time']);
    if ($result['Result'] == 'Failed') {
        $failure = $test->addChild('failure');
        $failure->addAttribute('type', 'junit.framework.AssertionFailedError');
        $failure->addAttribute('message', $result['Notes']);
    }
}

$dom = dom_import_simplexml($testSuite)->ownerDocument;
$dom->formatOutput = true;
echo $dom->saveXML();