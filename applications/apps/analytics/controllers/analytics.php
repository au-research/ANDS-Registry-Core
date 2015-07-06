<?php

/*
 * Analytics Module
 * for Data Source Report functionality
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Analytics extends MX_Controller
{

    function index()
    {
        $data = array(
            'title' => 'Analytics'
        );

        $data['scripts'] = array(
            'analytics_app'
        );

        $data['app_js_lib'] = array(
            'angular/angular.js',
            'Chart.js/Chart.min.js',
            'angular-chart.js/angular-chart.js',
            'moment/moment.js',
            'bootstrap-daterangepicker/daterangepicker.js',
            'angular-daterangepicker/js/angular-daterangepicker.js'
        );

        $data['app_css_lib'] = array(
            'angular-chart.js/dist/angular-chart.css',
            'bootstrap-daterangepicker/daterangepicker-bs2.css'
        );

        $data['js_lib'] = array('core');
        $this->load->view('analytics_app', $data);
    }


    function summary()
    {

        //header
//        $this->output->set_status_header(200);
        $this->output->set_header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        //capturing filters from AngularJS POST field
        $filters = false;
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata, true);
        $filters = isset($request['filters']) ? $request['filters'] : $filters;

        $result = array(
            'result' => array(),
            'filters' => $filters
        );

        $ranges = $this->date_range($filters['period'][0], $filters['period'][1], '+1day', 'Y-m-d');
        foreach ($ranges as $date) {
            $file_path = 'engine/logs/' . $filters['log'] . '/log-' . $filters['log'] . '-' . $date . '.php';
            $lines = $this->readfile($file_path);

            $result['result'][$date] = array(
                'total' => 0
            );

            foreach ($filters['dimensions'] as $dimension) {
                $result['result'][$date][$dimension] = 0;
            }

            foreach ($lines as $line) {
                $content = $this->read($line);
                $group = $filters['group'];
                $group_type = $group['type'];
                $group_value = $group['value'];

                if (isset($content[$group_type]) && $content[$group_type] == $group_value) {
                    if (!$this->isbot($content['user_agent'])) $result['result'][$date]['total']++;
                    foreach ($filters['dimensions'] as $d) {
                        if (isset($content['event']) && $content['event'] == $d) {
                            if (!$this->isbot($content['user_agent'])) $result['result'][$date][$d]++;
                        }
                    }
                }
            }

        }


        echo json_encode($result);
    }

    function isbot($useragent)
    {
        if (preg_match('/bot|crawl|slurp|spider/i', $useragent)) {
            return true;
        } else {
            return false;
        }
    }

    function get($dir = false)
    {
        $this->output->set_status_header(200);
        $this->output->set_header('Content-type: application/json');

        if (!$dir) throw new Exception('No Directory Specified');
        $date = $this->input->get('date') ?: false;
        $date_from = $this->input->get('date') ?: false;
        $date_to = $this->input->get('date') ?: false;
        $events = $this->input->get('events') ?: false;


        if ($events) {
            $events = explode('-', $events);
        }

        $result = array();
        if ($date) {
            $file_path = 'engine/logs/' . $dir . '/log-' . $dir . '-' . $date . '.php';
            $lines = $this->readfile($file_path);
            array_splice($lines, 100);
            $result[$date] = array();
            foreach ($lines as $line) {
                $content = $this->read($line);
                if (isset($events)) {
                    if (isset($content['event']) && in_array($content['event'], $events)) {
                        $result[$date][] = $content;
                    }
                } else {
                    $result[$date][] = $content;
                }
            }
        } else if ($date_from && $date_to) {
            $ranges = $this->date_range($date_from, $date_to, '+1day', 'Y-m-d');
        }
        echo json_encode(
            array(
                'status' => 'OK',
                'message' => $result
            )
        );
    }

    function readdir($directory)
    {
        $scanned_directory = array_diff(scandir($directory), array('..', '.'));
        return $scanned_directory;
    }

    function readfile($file)
    {
        $lines = array();
        if (file_exists($file)) {
            $file_handle = fopen($file, "r");
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                $lines[] = $line;
            }
            fclose($file_handle);
        }
        return $lines;
    }

    function read($string)
    {
        $result = array();
        preg_match_all("/\[([^\]]*)\]/", $string, $matches);
        foreach ($matches[1] as $match) {
            $array = explode(':', $match, 2);
            if ($array && is_array($array) && isset($array[0]) && isset($array[1])) {
                $result[$array[0]] = $array[1];
            }
        }
        return $result;
    }

    /**
     * Creating date collection between two dates
     *
     * <code>
     * <?php
     * # Example 1
     * date_range("2014-01-01", "2014-01-20", "+1 day", "m/d/Y");
     *
     * # Example 2. you can use even time
     * date_range("01:00:00", "23:00:00", "+1 hour", "H:i:s");
     * </code>
     *
     * @author Ali OYGUR <alioygur@gmail.com>
     * @param string since any date, time or datetime format
     * @param string until any date, time or datetime format
     * @param string step
     * @param string date of output format
     * @return array
     */
    function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y')
    {

        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {

            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }

    function __construct()
    {
        parent::__construct();
    }
}