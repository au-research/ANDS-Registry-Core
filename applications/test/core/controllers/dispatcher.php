<?php

class Dispatcher extends MX_Controller
{
    private $testPath;
    private $testResultPath;

    /**
     * Dispatcher constructor.
     */
    public function __construct()
    {
        parent::__construct();
        set_exception_handler('json_exception_handler');
        $this->testPath = APP_PATH;
        $this->testResultPath = 'test-reports';
    }

    /**
     * @param       $method
     * @param array $params
     */
    public function _remap($method, $params = array())
    {
        // Put the method back together and try and locate a matching controller
        array_unshift($params, $method);

        $this->load->library('unit_test');
        require_once(BASE . 'vendor/autoload.php');

        //get all testable modules (testsuites)
        $testableModules = array();
        $modules = $this->getTestableModule($this->testPath);
        foreach ($modules as $module) {
            $testableModules[$module] = $this->getTestsInModule($this->testPath, $module);
        }

        // Selective testing if params is specified that is not index or test
        $testSuites = array();
        $specificTest = null;
        if(sizeof($params) > 0) {
            if ($params[0]!='index' && $params[0]!='test') {
                foreach ($params as $suite) {
                    if (array_key_exists($suite, $testableModules)) {
                        $testSuites[$suite] = $testableModules[$suite];

                        // if comes in a form of php index.php test <suite> <specific>
                        if (isset($params[1]) && in_array($params[1], $testSuites[$suite])) {
                            // set specific test to the <specific> only if it matches
                            $specificTest = $params[1];
                        }
                    }
                }
            } else {
                $testSuites = $testableModules;
            }
        } else {
            $testSuites = $testableModules;
        }

        // create the test result path if not exists, prime for writing
        if (!file_exists($this->testResultPath)) {
            mkdir($this->testResultPath, 0744, true);
        }

        $this->benchmark->mark('start_testing');
        $results = array(
            'benchmark' => array(),
            'tests' => array()
        );
        if (sizeof($testSuites) > 0) {

            foreach ($testSuites as $testSuite=>$tests) {

                // only run the specific test provided
                if ($specificTest !== null) {
                    $tests = [$specificTest];
                }

                $specificTestFunction = array_key_exists(2, $params) ? $params[2] : false;

                $result = $this->run($testSuite, $tests, $specificTestFunction);
                $results['tests'][$testSuite] = $result;
                $result['testSuiteName'] = $testSuite;
                $JUnitXML = $this->load->view('junit-xml-report', $result, true);
                file_put_contents($this->testResultPath . '/'.$testSuite.'.xml', $JUnitXML);
            }
        }
        $this->benchmark->mark('end_testing');
        $results['benchmark'] = [
            'time' =>  $this->benchmark->elapsed_time('start_testing', 'end_testing', 5),
            'memory' => $this->benchmark->memory_usage()
        ];



        // display
        if (sizeof($testSuites) > 0) {
            if ($this->input->is_cli_request()) {
                $this->load->view('cli-test-report', $results);
            } else {
                echo json_encode($results, true);
            }
        }
    }

    /**
     * Run a particular testSuites given all the testcases
     * Returns results, elapsed and memory usage
     *
     * @param $testSuite
     * @param $tests
     * @param bool $specificTestFunction
     * @return array
     */
    private function run($testSuite, $tests, $specificTestFunction = false) {
        $this->benchmark->mark('start');

        // collect testable modules and run tests on them, append results
        $results = array();
        foreach ($tests as $testName) {
            require_once($this->testPath . $testSuite . '/' . $testName . '.php');
            $namespace = "ANDS\\Test\\";
            $className = $namespace . $testName;
            $testObject = new $className();
            $results = array_merge($results, $testObject->runTests($specificTestFunction));
            unset($testObject);
            $testObject = null;
        }

        $this->benchmark->mark('end');
        $elapsed = $this->benchmark->elapsed_time('start', 'end', 5);
        $memory = $this->benchmark->memory_usage();

        //collect data
        $data = [
            'results' => $results,
            'elapsed' => $elapsed,
            'memory' => $memory
        ];

        return $data;
    }


    /**
     * Returns a list of testable modules as an array
     *
     * @param $path
     * @return array
     */
    private function getTestableModule($path)
    {
        $tests = array();
        $exclude = ['.', '..', 'core', 'vendor'];
        if (is_dir($path)) {
            if ($dir_handler = opendir($path)) {
                while (($file = readdir($dir_handler)) !== false) {
                    if (filetype($path . $file) == 'dir' && !in_array($file, $exclude)) {
                        $tests[] = $file;
                    }
                }
                closedir($dir_handler);
            }
        }
        return $tests;
    }

    /**
     * Returns a list of tests belongs to a module
     *
     * @param $path
     * @param $module
     * @return array
     */
    private function getTestsInModule($path, $module)
    {
        $tests = array();
        $module_path = $path . $module . '/';
        if (is_dir($path) && $dir_handler = opendir($module_path)) {
            while (($file = readdir($dir_handler)) !== false) {
                if (filetype($module_path . $file) == 'file') {
                    $tests[] = preg_replace('/\.php$/', '', $file);
                }
            }
        }
        return $tests;
    }
}