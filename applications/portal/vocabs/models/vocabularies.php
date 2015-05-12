<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Vocabularies CI Model
 *
 * Used for creating vocabularies, viewing vocabularies and extending vocabularies metadata
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Vocabularies extends CI_Model {

	public function test_vocabs() {
		$test_vocab1 = new _vocabulary();
		$test_vocab1->populate(
			array (
                'id' =>'test1',
				'title' => 'ANZSRC Fields of Research',
                'acronym'=>'ANZSRC-FOR',
				'slug' => 'anzsrc-for',
				'vocab_uri' => 'http://vocabs.ands.org.au/anzsrc-for',
                'pool_party_id' => '1DCDF7D0-EFB1-0001-4A4A-2C0D1BB3199A',
                'top_concept'=>'research',
                'description'=>'',
                'licence' => 'conditional',
                'publisher_id'=>'1',
                'versions'=>array('title'=>'this is a version title',
                                  'status'=>'current',
                                  )
			)
		);

		$test_vocab2 = new _vocabulary();
		$test_vocab2->populate(
			array (
                'id' =>'test2',
				'title' => 'ANZSRC-SEO',
                'acronym'=>'ANZSRC-SEO',
				'slug' => 'anzsrc-seo',
				'vocab_uri' => 'http://vocabs.ands.org.au/anzsrc-seo',
                'pool_party_id' => '',
                'top_concept'=>'research',
                'description'=>'',
                'licence' => 'restricted',
                'publisher_id'=>'1',
                'versions'=>array('title'=>'this is a version title',
                    'status'=>'current',
                )
			)
		);


        $test_vocab3 = new _vocabulary();
        $test_vocab3->populate(
            array (
                'id' =>'test3',
                'title' => 'Registry Interchange Format - Collections and Services',
                'acronym'=>'RIFCS',
                'slug' => 'rifcs',
                'vocab_uri' => 'http://ands.poolparty.biz/rifcs',
                'pool_party_id' => '1DCE031F-808F-0001-378D-2D3E15E01889',
                'top_concept'=>'Data Collections',
                'description'=>'The Registry Interchange Format - Collections and Services (RIF-CS) Schema was developed as a data interchange format for supporting the electronic exchange of collection and service descriptions. It organises information about collections and services into the format required by the ANDS Collections Registry.',
                'licence' => 'open',
                'publisher_id'=>'1',
                'versions'=>array('title'=>'this is a version title',
                    'status'=>'current',
                )
            )
        );


		$test_records = array(
			'anzsrc-for' => $test_vocab1,
			'anzsrc-seo' => $test_vocab2,
            'rifcs' => $test_vocab3
		);

		return $test_records;
	}

	function __construct() {
		parent::__construct();
		include_once("_vocabulary.php");
	}
}