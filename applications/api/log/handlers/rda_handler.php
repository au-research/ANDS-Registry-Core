<?php
namespace ANDS\API\Log\Handler;

use \Exception as Exception;

/**
 * Handles log/rda
 * getGrants API
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class RdaHandler extends Handler
{

    function handle()
    {
        $result = array();

        $this->ci->load->library('ElasticSearch');

        $this->ci->elasticsearch
            ->init()
            ->setPath('/logs/production/_search');
        $this->ci->elasticsearch->setOpt('from', 0)->setOpt('size', 10)
            ->mustf('term', 'is_bot', 'false');


        $content = [
            'query' => [
                'term' => ['is_bot' => true]
            ]
        ];



        $content = [
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['event' => 'portal_search']],
                        ['term' => ['is_bot' => 'false']]
                    ]
                ]
            ]
        ];

//        $content = [
//            'query' => [
//                'filtered' => [
//                    'filter' => [
//                        'bool' => [
//                            'must' => [
//                                ['term' => ['is_bot' => false]],
//                                ['bool' => [
//                                    'should' => [
//                                        ['term' => ['group' => 'AMMRF']],
//                                        ['term' => ['group' => 'Australian Centre for Microscopy & Microanalysis']]
//                                    ]
//                                ]]
//                            ]
//                        ]
//                    ]
//                ]
//            ]
//        ];


        $result = $this->ci->elasticsearch->search($content);

        return $result;
    }

}