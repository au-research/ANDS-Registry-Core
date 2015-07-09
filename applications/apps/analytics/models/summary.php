<?php
/**
 * Summary class, use for Analytics of a single period of time
 * @todo Load analytics modules
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Summary extends CI_Model
{
    /**
     * Get the summary of a given filter set
     * @param  array $filters
     * @return array
     */
    public function get($filters)
    {
        $result = array();

        $ranges = date_range(
            $filters['period']['startDate'],
            $filters['period']['endDate'],
            '+1day', 'Y-m-d'
        );

        foreach ($ranges as $date) {

            //get the stat from various sources
            $lines = $this->getStatFromInternalLog($date, $filters);
            array_merge($lines, $this->getStatFromGoogle($date, $filters));

            //setting up values
            $result[$date] = array('total' => 0);
            foreach ($filters['dimensions'] as $dimension) {
                $result[$date][$dimension] = 0;
            }

            //process the lines
            foreach ($lines as $line) {
                $content = readString($line);

                $group = $filters['group'];
                $group_type = $group['type'];
                $group_value = $group['value'];

                if (isset($content[$group_type]) && $content[$group_type] == $group_value) {
                    if (!isbot($content['user_agent'])) {
                        $result[$date]['total']++;
                    }

                    foreach ($filters['dimensions'] as $d) {
                        if (isset($content['event']) && $content['event'] == $d) {
                            if (!isbot($content['user_agent'])) {
                                $result[$date][$d]++;
                            }

                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Return the statistic lines from the internal log collected via portal
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  string $date    Date for the event
     * @param  array $filters  filters passed down
     * @return array(lines)
     */
    private function getStatFromInternalLog($date, $filters)
    {
        $file_path = 'engine/logs/' . $filters['log'] . '/log-' . $filters['log'] . '-' . $date . '.php';
        $lines = readFileToLine($file_path);
        return $lines;
    }

    /**
     * Return the statistics lines from GoogleAnalytics
     * @todo
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param  string $date    Date for the event
     * @param  string $filters filters passed down
     * @return array(lines)
     */
    private function getStatFromGoogle($date, $filters)
    {
        return array();
    }

    //boring class construction
    public function __construct()
    {
        parent::__construct();
    }
}
