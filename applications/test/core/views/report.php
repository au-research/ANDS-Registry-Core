<?php

$pass = 0;
$fail = 0;
$assertions = 0;
$failedTest = array();
foreach ($results as $result) {
    if ($result['Result']=='Passed') {
        $pass++;
    } elseif ($result['Result']=='Failed') {
        $fail++;
        $failedTest[] = $result;
    }
    $assertions++;
}

echo "There are $assertions assertions \n$pass passed \n$fail failed \n";
if (sizeof($failedTest) > 0) {
    var_dump($failedTest);
}