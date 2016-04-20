<?php

class Dispatcher extends MX_Controller
{
    /**
     * Dispatcher constructor.
     */
    public function __construct()
    {
        parent::__construct();
        set_exception_handler('json_exception_handler');
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
        require_once(APP_PATH . 'vendor/autoload.php');

        $this->benchmark->mark('start');

        //list directories and find testable modules
        $testableModules = array();
        $modules = $this->getTestableModule(APP_PATH);
        foreach ($modules as $module) {
            $testableModules[$module] = $this->getTestsInModule(APP_PATH, $module);
        }

        // collect testable modules and run tests on them, append results
        $results = array();
        foreach ($testableModules as $module => $tests) {
            foreach ($tests as $testName) {
                require_once(APP_PATH . $module . '/' . $testName . '.php');
                $namespace = "ANDS\\Test\\";
                $className = $namespace . $testName;
                $testObject = new $className();
                $results = array_merge($results, $testObject->runTests());
            }
        }

        $this->benchmark->mark('end');
        $elapsed = $this->benchmark->elapsed_time('start', 'end');
        $memory = memory_get_peak_usage(true);

        //collect data
        $data = [
            'results' => $results,
            'elapsed' => $elapsed,
            'memory' => $memory
        ];

        // display
        if ($this->input->is_cli_request()) {
            $this->load->view('cli-test-report', $data);
        }

        $JUnitXML = $this->load->view('junit-xml-report', $data, true);
        if (!file_exists('test-reports/')) {
            mkdir('test-reports/', 0777, true);
        }
        if (file_exists('test-reports/junit-xml-report.xml')) {
            unlink('test-reports/junit-xml-report.xml');
        }
        file_put_contents('test-reports/' . 'junit-xml-report.xml', $JUnitXML, FILE_APPEND);
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