<?php


$pass = 0;
$fail = 0;
$assertions = 0;
$failedTest = array();
$testNames = array();
foreach ($tests as $module=>$results) {
    foreach ($results['results'] as $result) {
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
}
$testCount = sizeof($testNames);

// formating
echo "Testsuites: " . implode(', ', array_keys($tests)). "\n";
echo "Time: $elapsed, Memory: $memory\n\n";

if (sizeof($failedTest) > 0) {
    echo "$fail failure: \n";
    foreach ($failedTest as $counter => $failed) {
        $counter++;
        echo "\n";
        echo $counter . ") " . $failed['Test Name'] . "\n";
        echo isset($failed['Notes']) ? $failed['Notes'] : "";
        echo "\n";
    }
    echo "\n\nFAILURES!\n";
    echo "Tests: $testCount, Assertions: $assertions, Failures: $fail";
} else {
    echo "OK ($testCount tests, $assertions assertions)";
}
echo "\n";