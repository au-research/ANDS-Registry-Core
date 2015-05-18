<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Vocabularies CI Model
 *
 * Used for creating vocabularies, viewing vocabularies and extending vocabularies metadata
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Vocabularies extends CI_Model {

    /**
     * Returns a set of test vocabulary used for testing purposes
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @author Liz Woods <liz.woods@ands.org.au>
     * @return array(_vocabulary) a PHP array of _vocabulary object
     * @see applications/vocabs/models/_vocabulary
     */
	public function test_vocabs() {
		$test_vocab1 = new _vocabulary();
		$test_vocab1->populate(
			array (
                'id' =>'test1',
				'title' => 'ANZSRC Fields of Research',
                'acronym'=>'ANZSRC-FOR',
                'note'=>'Just a little notes baout this vocab',
                'logo'=>'https://devl.ands.org.au/workareas/liz/core/assets/core/images/footer_logo_rev.png',
                'creation_date'=>'01-01-2009',
                'revision_cycle'=>'annual',
                'language'=>array(0=>'En',1=>'Fr'),
				'slug' => 'anzsrc-for',
				'vocab_uri' => 'http://vocabs.ands.org.au/anzsrc-for',
                'pool_party_id' => '1DCDF7D0-EFB1-0001-4A4A-2C0D1BB3199A',
                'top_concept'=>array(0=>'Research'),
                'description'=>'',
                'licence' => $this->checkRightsText('http://creativecommons.org/licenses/by/4.0/'),
                'related_entity'=>array( 0=>array( 'id'=>'1',
                    'type'=>'publisher',
                    'relationship'=>'publisherOf',
                    'title'=>'Bureau of Statistics',
                    'email'=>'services@ands.org.au',
                    'phone'=>'0224567893',
                    'address'=>'123 Some Street, Canberra ACT, 2606',
                    'URL'=>'http://ands.org.au'),
                    1=>array( 'id'=>'2',
                        'type'=>'contributor',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'),
                    2=>array( 'id'=>'3',
                        'type'=>'service',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'),
                    3=>array( 'id'=>'4',
                        'type'=>'vocab',
                        'relationship'=>'derviedFrom',
                        'title'=>'my special vocab',

                ),
                'versions'=>array(
                    0=>array(
                        'title'=>'this is a version title',
                           'status'=>'current',
                            'release_date'=>'01-03-2015',
                            'note'=>'Just a little bit more info on the version',
                            'id'=>'23',
                            'access_point'=>array(
                                'access_point_type'=>'webPage',
                                'access_point_format'=>'XML',
                                'access_point_URI'=>'http://some.web.access/file'
                            )
                          ),
                      1=>array(
                        'title'=>'this is an older version title',
                        'status'=>'superceded',
                        'release_date'=>'01-03-2015',
                        'note'=>'Just a little bit more info on the version',
                        'id'=>'23',
                        'access_point'=>array(
                          'access_point_type'=>'webPage',
                          'access_point_format'=>'XML',
                          'access_point_URI'=>'http://some.web.access/file'
                        )
                      ),
                      2=>array('title'=>'this is an older version title',
                          'status'=>'depreciated',
                          'release_date'=>'01-03-2015',
                          'note'=>'Just a little bit more info on the version',
                          'id'=>'23',
                          'access_point'=>array(
                              'access_point_type'=>'webPage',
                              'access_point_format'=>'XML',
                              'access_point_URI'=>'http://some.web.access/file'
                          )
                      )
                  ),
                'subjects'=>array(0=>array('subject'=>'My subject','subject_source'=>'ANZSRC'),
                                  1=>array('subject'=>'Earth','subject_source'=>'ANZSRC'),
                                  2=>array('subject'=>'Fish','subject_source'=>'ANZSRC'),
                                  3=>array('subject'=>'Water','subject_source'=>'ANZSRC'),
                                  4=>array('subject'=>'Stars','subject_source'=>'ANZSRC')

                                ),

            )

		);

		$test_vocab2 = new _vocabulary();
		$test_vocab2->populate(
			array (
                'id' =>'test2',
				'title' => 'ANZSRC-SEO',
                'acronym'=>'ANZSRC-SEO',
                'note'=>'Just a little notes baout this vocab',
                'logo'=>'https://devl.ands.org.au/workareas/liz/core/assets/core/images/footer_logo_rev.png',
                'creation_date'=>'01-01-2009',
                'revision_cycle'=>'quarterly',
                'language'=>array(0=>'En',1=>'Fr'),
				'slug' => 'anzsrc-seo',
				'vocab_uri' => 'http://vocabs.ands.org.au/anzsrc-seo',
                'pool_party_id' => '',
                'top_concept'=>array(0=>'Research'),
                'description'=>'',
                'licence' => $this->checkRightsText('http://creativecommons.org/licenses/by/4.0/'),
                'related_entity'=>array( 0=>array( 'id'=>'1',
                    'type'=>'publisher',
                    'relationship'=>'publisherOf',
                    'title'=>'Bureau of Statistics',
                    'email'=>'services@ands.org.au',
                    'phone'=>'0224567893',
                    'address'=>'123 Some Street, Canberra ACT, 2606',
                    'URL'=>'http://ands.org.au'),
                    1=>array( 'id'=>'2',
                        'type'=>'contributor',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'),
                    2=>array( 'id'=>'3',
                        'type'=>'service',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'),
                    3=>array( 'id'=>'4',
                        'type'=>'vocab',
                        'relationship'=>'derviedFrom',
                        'title'=>'my special vocab',

                    ),
                'versions'=>array(0=>array('title'=>'this is a version title',
                                        'status'=>'current',
                                        'release_date'=>'01-03-2015',
                                        'note'=>'Just a little bit more info on the version',
                                        'id'=>'23',
                                        'access_point'=>array(
                                            'access_point_type'=>'webPage',
                                            'access_point_format'=>'XML',
                                            'access_point_URI'=>'http://some.web.access/file'
                                            )
                                    ),
                                    1=>array('title'=>'this is an older version title',
                                        'status'=>'superceded',
                                        'release_date'=>'01-03-2015',
                                        'note'=>'Just a little bit more info on the version',
                                        'id'=>'23',
                                        'access_point'=>array(
                                            'access_point_type'=>'webPage',
                                            'access_point_format'=>'XML',
                                            'access_point_URI'=>'http://some.web.access/file'
                                            )
                                        ),
                                        2=>array('title'=>'this is an older version title',
                                            'status'=>'depreciated',
                                            'release_date'=>'01-03-2015',
                                            'note'=>'Just a little bit more info on the version',
                                            'id'=>'23',
                                            'access_point'=>array(
                                                'access_point_type'=>'webPage',
                                                'access_point_format'=>'XML',
                                                'access_point_URI'=>'http://some.web.access/file'
                                            )
                                        )
                        ),
                'subjects'=>array(0=>array('subject'=>'My subject','subject_source'=>'ANZSRC'),
                    1=>array('subject'=>'Earth','subject_source'=>'ANZSRC'),
                    2=>array('subject'=>'Fish','subject_source'=>'ANZSRC'),
                    3=>array('subject'=>'Water','subject_source'=>'ANZSRC'),
                    4=>array('subject'=>'Stars','subject_source'=>'ANZSRC')

                ),
                )

		);


        $test_vocab3 = new _vocabulary();
        $test_vocab3->populate(
            array (
                'id' =>'test3',
                'title' => 'Registry Interchange Format - Collections and Services',
                'acronym'=>'RIFCS',
                'note'=>'Just a little notes baout this vocab',
                'logo'=>'https://devl.ands.org.au/workareas/liz/core/assets/core/images/footer_logo_rev.png',
                'creation_date'=>'01-01-2009',
                'revision_cycle'=>'annual',
                'language'=>array(0=>'En',1=>'Fr'),
                'slug' => 'rifcs',
                'vocab_uri' => 'http://ands.poolparty.biz/rifcs',
                'pool_party_id' => '1DCE031F-808F-0001-378D-2D3E15E01889',
                'top_concept'=>array(0=>'Data Collections',1=>'Linked Data',2=>'Data Management'),
                'description'=>'The Registry Interchange Format - Collections and Services (RIF-CS) Schema was developed as a data interchange format for supporting the electronic exchange of collection and service descriptions. It organises information about collections and services into the format required by the ANDS Collections Registry.',
                'licence' => $this->checkRightsText('http://creativecommons.org/licenses/by/4.0/'),
                'related_entity'=>array( 0=>array( 'id'=>'1',
                    'type'=>'publisher',
                    'relationship'=>'publisherOf',
                    'title'=>'Bureau of Statistics',
                    'email'=>'services@ands.org.au',
                    'phone'=>'0224567893',
                    'address'=>'123 Some Street, Canberra ACT, 2606',
                    'URL'=>'http://ands.org.au'),
                    1=>array( 'id'=>'2',
                        'type'=>'contributor',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'),
                    2=>array( 'id'=>'3',
                        'type'=>'service',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'),
                    3=>array( 'id'=>'4',
                        'type'=>'vocab',
                        'relationship'=>'derviedFrom',
                        'title'=>'my special vocab',

                    ),
                'versions'=>array(0=>array('title'=>'this is a version title',
                    'status'=>'current',
                    'release_date'=>'01-03-2015',
                    'note'=>'Just a little bit more info on the version',
                    'id'=>'23',
                    'access_point'=>array(
                        'access_point_type'=>'webPage',
                        'access_point_format'=>'XML',
                        'access_point_URI'=>'http://some.web.access/file'
                        )
                    ),
                    1=>array('title'=>'this is an older version title',
                        'status'=>'superceded',
                        'release_date'=>'01-03-2015',
                        'note'=>'Just a little bit more info on the version',
                        'id'=>'23',
                        'access_point'=>array(
                            'access_point_type'=>'webPage',
                            'access_point_format'=>'XML',
                            'access_point_URI'=>'http://some.web.access/file'
                        )
                    ),
                    2=>array('title'=>'this is an older version title',
                        'status'=>'depreciated',
                        'release_date'=>'01-03-2015',
                        'note'=>'Just a little bit more info on the version',
                        'id'=>'23',
                        'access_point'=>array(
                            'access_point_type'=>'webPage',
                            'access_point_format'=>'XML',
                            'access_point_URI'=>'http://some.web.access/file'
                        )
                    )
                ),
                'subjects'=>array(0=>array('subject'=>'My subject','subject_source'=>'ANZSRC'),
                    1=>array('subject'=>'Earth','subject_source'=>'ANZSRC'),
                    2=>array('subject'=>'Fish','subject_source'=>'ANZSRC'),
                    3=>array('subject'=>'Water','subject_source'=>'ANZSRC'),
                    4=>array('subject'=>'Stars','subject_source'=>'ANZSRC')

                ),
            )
        );


		$test_records = array(
			'anzsrc-for' => $test_vocab1,
			'anzsrc-seo' => $test_vocab2,
            'rifcs' => $test_vocab3
		);

		return $test_records;
	}
    function checkRightsText($value)
    {

        if(str_replace("http://creativecommons.org/licenses/by/","",$value)!=$value)
        {
            return "CC-BY";
        }
        elseif(str_replace("http://creativecommons.org/licenses/by-sa/","",$value)!=$value)
        {
            return "CC-BY-SA";
        }
        elseif(str_replace("http://creativecommons.org/licenses/by-nc/","",$value)!=$value)
        {
            return "CC-BY-NC";
        }
        elseif(str_replace("http://creativecommons.org/licenses/by-nc-sa/","",$value)!=$value)
        {
            return "CC-BY-NC-SA";
        }
        elseif(str_replace("http://creativecommons.org/licenses/by-nd/","",$value)!=$value)
        {
            return "CC-BY-ND";
        }
        elseif(str_replace("http://creativecommons.org/licenses/by-nc-nd/","",$value)!=$value)
        {
            return "CC-BY-NC-ND";
        }
        else
        {
            return $value;
        }
    }

    /**
     * Constructor Method
     * Autoload the _vocabulary class
     * @ignore
     */
	function __construct() {
		parent::__construct();
		include_once("_vocabulary.php");
	}
}