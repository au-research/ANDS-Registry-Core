<?php
/**
 * Summary class, use for Analytics of a single period of time
 * @todo Load analytics modules
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Summary extends CI_Model
{

    public function get($filters) {
        $this->load->library('ElasticSearch');
        $filters['period']['startDate'] = date('Y-m-d', strtotime($filters['period']['startDate']));
        $filters['period']['endDate'] = date('Y-m-d', strtotime($filters['period']['endDate']));
        $this->elasticsearch->init()->setPath('/logs/production/_search');
        $this->elasticsearch
            ->setOpt('from', 0)->setOpt('size', 50000)
            ->andf('term', $filters['group']['type'], $filters['group']['value'])
            ->andf('range', 'date',
                array (
                    'from' => $filters['period']['startDate'],
                    'to' => $filters['period']['endDate']
                )
            );

        //dimensions
        $this->elasticsearch
            ->setAggs('date',
                array('date_histogram' =>
                    array(
                        'field'=>'date',
                        'format' => 'yyyy-MM-dd',
                        'interval' => 'day',
                        // 'aggs'=>array(
                        //     'event' => array('terms'=>array('field'=>'event'))
                        // )
                    )
                )
            )
            ->setAggs('event',
                array('value_count'=>array('field'=>'event'))
            )
            ;
        // $this->elasticsearch
        //     ->setFacet('group', array('terms'=>array('field'=>'group')))
        //     ->setFacet('event', array('terms'=>array('field'=>'event')));

        $search_result = $this->elasticsearch->search();
        // dd($filters);
        // dd($search_result);


        $ranges = date_range(
            $filters['period']['startDate'],
            $filters['period']['endDate'],
            '+1day', 'Y-m-d'
        );

        $result = array();

        foreach ($ranges as $date) {
            $result[$date] = array('total' => 0);
            foreach ($filters['dimensions'] as $dimension) {
                $result[$date][$dimension] = 0;
            }
        }

        foreach ($search_result['hits']['hits'] as $hit) {
            $content = $hit['_source'];
            $date = date('Y-m-d', strtotime($content['date']));
            if (!isbot($content['user_agent'])) {
                $result[$date]['total']++;
            }
            foreach ($filters['dimensions'] as $dimension) {
                if (!isbot($content['user_agent']) && $content['event']==$dimension) {
                    $result[$date][$dimension]++;
                }
            }
        }
        return $result;
    }

    /**
     * Get the summary of a given filter set
     * @param  array $filters
     * @return array
     */
    public function get_deprecate($filters)
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
                $line = json_encode($line);
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

    public function getStatFromES($date, $filters = false) {
        $result = array();
        $filters = [
            'query'=> [
                'match' => [
                    'date' => $date
                ]
            ]
        ];
        $response = curl_post('http://localhost:9200/logs/production/_search', json_encode($filters));
        $response = json_decode($response, true);
        if (isset($response['hits'])) {
           foreach($response['hits']['hits'] as $doc) {
                $result[] = $doc['_source'];
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
    public function getStatFromInternalLog($date, $filters)
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
