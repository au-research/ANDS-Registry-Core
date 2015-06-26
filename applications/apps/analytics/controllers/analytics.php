<?php

class Analytics extends MX_Controller
{

    function index()
    {
        $this->output->set_status_header(200);
        $this->output->set_header('Content-type: application/json');

        $directory = 'engine/logs/portal/';
        $files = $this->readdir($directory);
        sort($files);


        //from 2015-06-01 to 2015-06-12
        $ranges = $this->date_range('2015-06-01', '2015-06-12', '+1day', 'Y-m-d');
        dd($ranges);

        array_splice($files, 1);

        $results = array();
        foreach ($files as $file) {
            $content = $this->readfile($directory . $file);
            foreach ($content as $line) {
                $result = $this->read($line);
                $results[] = $result;
            }
        }
        echo json_encode($results);
    }

    function summary2()
    {

        //header
        $this->output->set_status_header(200);
        $this->output->set_header('Content-type: application/json');

        $result = array();

        $filters = array(
            'log' => 'portal',
            'period' => array('2015-06-01', '2015-06-01'),
            'dimensions' => array(
                array('group', 'portal_view'),
                array('group', 'portal_page'),
                array('group', 'portal_search'),
            ),
            'unique' => false
        );

        //period is a single date for now
        $date = $filters['period'][0];

        //setup the result dimensions
        $result['filters'] = $filters;

        $file_path = 'engine/logs/' . $filters['log'] . '/log-' . $filters['log'] . '-' . $date . '.php';
        $lines = $this->readfile($file_path);

        foreach ($lines as $line) {
            $content = $this->read($line);
            foreach ($filters['dimensions'] as $dimension) {
                $d1 = $dimension[0];
                $d2 = $dimension[1];

                if (isset($content[$d1])) {
                    if (isset($result[$d1][$content[$d1]])) {
                        $result[$d1][$content[$d1]]['count'] += 1;
                        if (isset($content['event']) && $content['event'] == $d2) {
                            if (isset($result[$d1][$content[$d1]][$d2])) {
                                $result[$d1][$content[$d1]][$d2] += 1;
                            } else {
                                $result[$d1][$content[$d1]][$d2] = 1;
                            }
                        }
                    } else {
                        $result[$d1][$content[$d1]] = array(
                            'count' => 1,
                            $d2 => 1
                        );
                    }
                }
            }
        }

        echo json_encode($result);
    }

    function summary($dir)
    {
        $this->output->set_status_header(200);
        $this->output->set_header('Content-type: application/json');

        if (!$dir) throw new Exception('No Directory Specified');
        $date = $this->input->get('date') ?: false;
        $date_from = $this->input->get('date_from') ?: false;
        $date_to = $this->input->get('date_to') ?: false;
        $events = $this->input->get('events') ?: false;
        $extra = $this->input->get('extra') ?: false;

        if ($events) {
            $events = explode('-', $events);
        }

        $result = array();

        if ($date) {
            $file_path = 'engine/logs/' . $dir . '/log-' . $dir . '-' . $date . '.php';
            $lines = $this->readfile($file_path);
            $result[$date] = array();
            if ($events) {
                foreach ($events as $event) {
                    $result[$date][$event] = array(
                        'total' => 0,
                        'unique' => 0
                    );
                }
            }

            $ips = array();
            $ip_behaviour = array();
            foreach ($lines as $line) {
                $content = $this->read($line);
                if ($events) {
                    if (isset($content['event']) && in_array($content['event'], $events)) {
                        $result[$date][$content['event']]++;
                    }
                } else {
                    if (isset($content['event'])) {
                        if (isset($result[$date][$content['event']])) {

                            //increase total
                            $result[$date][$content['event']]['total'] = $result[$date][$content['event']]['total'] + 1;

                            //increase unique if unique by ip
                            if (isset($content['ip'])) {
                                $ip = $content['ip'];
                                if (!in_array($ip, $ips)) {
                                    $result[$date][$content['event']]['unique'] = $result[$date][$content['event']]['unique'] + 1;
                                }
                                array_push($ips, $ip);

                                //record ip behaviour
                                if (isset($ip_behaviour[$ip])) {
                                    $ip_behaviour[$ip]['access'] = $ip_behaviour[$ip]['access'] + 1;
                                    if (!in_array($content['user_agent'], $ip_behaviour[$ip]['user_agents'])) {
                                        $ip_behaviour[$ip]['user_agents'][] = $content['user_agent'];
                                    }
                                } else {
                                    $ip_behaviour[$ip] = array(
                                        'access' => 1,
                                        'user_agents' => array($content['user_agent'])
                                    );
                                }
                            }
                        } else {
                            $result[$date][$content['event']] = array(
                                'total' => 0,
                                'unique' => 0
                            );
                        }
                    }
                }
            }
            if ($extra == 'clients') {
                $result['clients'] = [
                    'total' => sizeof($ips),
                    'details' => $ip_behaviour
                ];
            }

        } else if ($date_from && $date_to) {

            $ranges = $this->date_range($date_from, $date_to, '+1day', 'Y-m-d');
            foreach ($ranges as $date) {
                $file_path = 'engine/logs/' . $dir . '/log-' . $dir . '-' . $date . '.php';
                $lines = $this->readfile($file_path);
                $result[$date] = array();
                if ($events) {
                    foreach ($events as $event) {
                        $result[$date][$event] = 0;
                    }
                }

                foreach ($lines as $line) {
                    $content = $this->read($line);
                    if ($events) {
                        if (isset($content['event']) && in_array($content['event'], $events)) {
                            $result[$date][$content['event']]++;
                        }
                    } else {
                        if (isset($content['event'])) {
                            if (isset($result[$date][$content['event']])) {
                                $result[$date][$content['event']] = $result[$date][$content['event']] + 1;
                            } else {
                                $result[$date][$content['event']] = 0;
                            }
                        }
                    }
                }
            }
        }

        echo json_encode(
            array(
                'status' => 'OK',
                'message' => $result
            )
        );
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

        $filters = $this->input->get('events') ?: false;


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