<?php

class Dispatcher extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        set_exception_handler('json_exception_handler');
    }

    public function _remap($method, $params = array())
    {
        // Put the method back together and try and locate a matching controller
        array_unshift($params, $method);

        $this->load->library('unit_test');

        $this->benchmark->mark('start');

        //list directories
        $testableModules = array();
        $modules = $this->getTestableModule(APP_PATH);
        foreach ($modules as $module) {
            $testableModules[$module] = $this->getTestsInModule(APP_PATH, $module);
        }

        $results = array();
        foreach ($testableModules as $module=>$tests) {
            foreach ($tests as $testName) {
                require_once(APP_PATH.$module.'/'.$testName.'.php');
                $testObject = new $testName();
                $results = array_merge($results, $testObject->runTests());
            }
        }

        $this->benchmark->mark('end');
        $elapsed = $this->benchmark->elapsed_time('start', 'end');
        $memory = memory_get_peak_usage(true);

        dd($results);

        $pass = 0;
        $fail = 0;
        $assertions = 0;
        $failedTest = array();
        $testNames = array();
        foreach ($results as $result) {
            if ($result['Result']=='Passed') {
                $pass++;
            } elseif ($result['Result']=='Failed') {
                $fail++;
                $failedTest[] = $result;
            }
            if (!in_array($result['Test Name'], $testNames)) {
                $testNames[] = $result['Test Name'];
            }
            $assertions++;
        }
        $testCount = sizeof($testNames);

        // formating

        echo "Time: $elapsed, Memory: $memory\n\n";

        if (sizeof($failedTest) > 0) {
            echo "$fail failure: \n";
            foreach ($failedTest as $counter=>$failed) {
                $counter++;
                echo "\n";
                echo $counter.") ".$failed['Test Name']."\n";
                echo isset($failed['Notes']) ? $failed['Notes'] : "";
                echo "\n";
            }
            echo "\n\nFAILURES!\n";
            echo "Tests: $testCount, Assertions: $assertions, Failures: $fail";
        } else {
            echo "OK ($testCount tests, $assertions assertions)";
        }
        echo "\n";

    }

    private function getTestableModule($path){
        $tests = array();
        if (is_dir($path)) {
            if ($dir_handler = opendir($path)) {
                while (($file = readdir($dir_handler)) !== false) {
                    if (filetype($path . $file)=='dir'
                        && $file!='.'
                        && $file!='..'
                        && $file!='core'
                    ) {
                        $tests[] = $file;
                    }
                }
                closedir($dir_handler);
            }
        }
        return $tests;
    }

    private function getTestsInModule($path, $module){
        $tests = array();
        $module_path = $path.$module.'/';
        if (is_dir($path) && $dir_handler = opendir($module_path)) {
            while (($file = readdir($dir_handler)) !== false) {
                if (filetype($module_path. $file)=='file') {
                    $tests[] = preg_replace('/\.php$/', '', $file);
                }
            }
        }
        return $tests;
    }
}