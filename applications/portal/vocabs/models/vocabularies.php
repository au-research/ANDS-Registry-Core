<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Vocabularies CI Model
 *
 * Used for creating vocabularies, viewing vocabularies and extending vocabularies metadata
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Vocabularies extends CI_Model {

    /**
     * Returns a single _vocabulary object by ID
     * @param  int $id 
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return _vocabulary     
     */
    public function getByID($id) {
        $vocab = new _vocabulary($id);
        return $vocab;
    }

    /**
     * Returns a single _vocabulary by SLUG
     * SLUG has to be unique as it maps to ID
     * This function calls the @getByID function internally
     * @param  string $slug 
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return _vocabulary       
     */
    public function getBySlug($slug) {
        $this->vocab_db = $this->load->database('vocabs', true);
        $result = $this->vocab_db->get_where('vocabularies', array('slug'=>$slug));
        if ($result->num_rows() > 0) {
            $vocab_result = $result->result_array();
            $vocab_id = $vocab_result[0]['id'];
            return $this->getByID($vocab_id);
        } else {
            return false;
        }
    }

    /**
     * Returns all vocabularies we have in the database
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @return array(_vocabulary)
     */
    public function getAll() {
        $this->vocab_db = $this->load->database('vocabs', true);
        $result = $this->vocab_db->get('vocabularies');
        if ($result->num_rows() == 0) return false;
        $res = array();
        foreach($result->result() as $r) {
            $vocab = $this->getByID($r->id);
            array_push($res, $vocab);
        }
        return $res;
    }

    public function getOwned() {
        $result = array();
        if ($this->user->isLoggedIn()) {
            $this->vocab_db = $this->load->database('vocabs', true);
            if($this->user->hasFunction('REGISTRY_SUPERUSER')){
                $query = $this->vocab_db->get('vocabularies');
                if ($query && $query->num_rows() > 0) {
                    foreach($query->result_array() as $r) {
                        $result[] = $r;
                    }
                }
            }else{
                $affiliations = $this->user->affiliations();
                $role_id = $this->user->localIdentifier();

                $query = $this->vocab_db->where_in('owner', $affiliations)->get('vocabularies');
                if ($query && $query->num_rows() > 0) {
                    foreach($query->result_array() as $r) {
                        $result[] = $r;
                    }
                }
                $query = $this->vocab_db->where_in('owner', $role_id)->get('vocabularies');
                if ($query && $query->num_rows() > 0) {
                    foreach($query->result_array() as $r) {
                        $result[] = $r;
                    }
                }
            }
        } else {
            //not logged in, no owned vocabularies
        }
        return $result;
    }

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
                'id' =>false,
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
                'related_entity'=>array(
                    0=>array( 'id'=>'1',
                    'type'=>'publisher',
                    'relationship'=>'publisherOf',
                    'title'=>'Bureau of Statistics',
                    'email'=>'services@ands.org.au',
                    'phone'=>'0224567893',
                    'address'=>'123 Some Street, Canberra ACT, 2606',
                    'URL'=>'http://ands.org.au'
                    ),
                    1=>array( 'id'=>'2',
                        'type'=>'contributor',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'
                    ),
                    2=>array( 'id'=>'3',
                        'type'=>'service',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'
                    ),
                    3=>array( 'id'=>'4',
                        'type'=>'vocab',
                        'relationship'=>'derviedFrom',
                        'title'=>'my special vocab',

                    )
                ),
                'versions'=>array(
                    0=>array(
                        'title'=>'this is a version title',
                           'status'=>'current',
                            'release_date'=>'01-03-2015',
                            'note'=>'Just a little bit more info on the version',
                            'id'=>'23',
                            'access_point'=>array(0=>array(
                                'access_point_type'=>'webPage',
                                'access_point_format'=>'XML',
                                'access_point_URI'=>'http://some.web.access/file'
                            )
                            )
                          ),
                      1=>array(
                        'title'=>'this is an older version title',
                        'status'=>'superceded',
                        'release_date'=>'01-03-2015',
                        'note'=>'Just a little bit more info on the version',
                        'id'=>'23',
                        'access_point'=>array(0=>array(
                          'access_point_type'=>'webPage',
                          'access_point_format'=>'XML',
                          'access_point_URI'=>'http://some.web.access/file'
                        )
                        )
                      ),
                      2=>array('title'=>'this is an older version title',
                          'status'=>'depreciated',
                          'release_date'=>'01-03-2015',
                          'note'=>'Just a little bit more info on the version',
                          'id'=>'23',
                          'access_point'=>array(0=>array(
                              'access_point_type'=>'webPage',
                              'access_point_format'=>'XML',
                              'access_point_URI'=>'http://some.web.access/file'
                          )

                          )
                      )
                  ),
                'subjects'=>array(0=>array('subject'=>'My subject','subject_source'=>'ANZSRC'),
                                  1=>array('subject'=>'Earth','subject_source'=>'ANZSRC'),
                                  2=>array('subject'=>'Fish','subject_source'=>'ANZSRC'),
                                  3=>array('subject'=>'Water','subject_source'=>'ANZSRC'),
                                  4=>array('subject'=>'Stars','subject_source'=>'ANZSRC')

                                )

            )

		);




		$test_vocab2 = new _vocabulary();
		$test_vocab2->populate(
			array (
                'id' =>false,
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
                'related_entity'=>array(
                    0=>array('id'=>'1',
                    'type'=>'publisher',
                    'relationship'=>'publisherOf',
                    'title'=>'Bureau of Statistics',
                    'email'=>'services@ands.org.au',
                    'phone'=>'0224567893',
                    'address'=>'123 Some Street, Canberra ACT, 2606',
                    'URL'=>'http://ands.org.au'
                    ),
                    1=>array('id'=>'2',
                        'type'=>'contributor',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'
                    ),
                    2=>array('id'=>'3',
                        'type'=>'service',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'
                    ),
                    3=>array( 'id'=>'4',
                        'type'=>'vocab',
                        'relationship'=>'derviedFrom',
                        'title'=>'my special vocab'
                         )
                    ),
                'versions'=>array(0=>array('title'=>'this is a version title',
                                        'status'=>'current',
                                        'release_date'=>'01-03-2015',
                                        'note'=>'Just a little bit more info on the version',
                                        'id'=>'23',
                                        'access_point'=>array(0=>array(
                                            'access_point_type'=>'webPage',
                                            'access_point_format'=>'XML',
                                            'access_point_URI'=>'http://some.web.access/file'
                                            )
                                        )
                                    ),
                                    1=>array('title'=>'this is an older version title',
                                        'status'=>'superceded',
                                        'release_date'=>'01-03-2015',
                                        'note'=>'Just a little bit more info on the version',
                                        'id'=>'23',
                                        'access_point'=>array(0=>array(
                                            'access_point_type'=>'webPage',
                                            'access_point_format'=>'XML',
                                            'access_point_URI'=>'http://some.web.access/file'
                                            )
                                            )
                                        ),
                                        2=>array('title'=>'this is an older version title',
                                            'status'=>'depreciated',
                                            'release_date'=>'01-03-2015',
                                            'note'=>'Just a little bit more info on the version',
                                            'id'=>'23',
                                            'access_point'=>array(0=>array(
                                                'access_point_type'=>'webPage',
                                                'access_point_format'=>'XML',
                                                'access_point_URI'=>'http://some.web.access/file'
                                            )
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
                'id' =>false,
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
                'related_entity'=>array(
                    0=>array( 'id'=>'1',
                        'type'=>'publisher',
                        'relationship'=>'publisherOf',
                        'title'=>'Bureau of Statistics',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'
                    ),
                    1=>array( 'id'=>'2',
                        'type'=>'contributor',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'
                        ),
                    2=>array( 'id'=>'3',
                        'type'=>'service',
                        'relationship'=>'hasContributor',
                        'title'=>'Joan Rivers',
                        'email'=>'services@ands.org.au',
                        'phone'=>'0224567893',
                        'address'=>'123 Some Street, Canberra ACT, 2606',
                        'URL'=>'http://ands.org.au'
                    ),
                    3=>array( 'id'=>'4',
                        'type'=>'vocab',
                        'relationship'=>'derviedFrom',
                        'title'=>'my special vocab'
                    )

                    ),
                'versions'=>array(0=>array('title'=>'this is a version title',
                    'status'=>'current',
                    'release_date'=>'01-03-2015',
                    'note'=>'Just a little bit more info on the version',
                    'id'=>'23',
                    'access_point'=>array(0=>array(
                        'access_point_type'=>'webPage',
                        'access_point_format'=>'XML',
                        'access_point_URI'=>'http://some.web.access/file'
                        )
                     )
                    ),
                    1=>array('title'=>'this is an older version title',
                        'status'=>'superceded',
                        'release_date'=>'01-03-2015',
                        'note'=>'Just a little bit more info on the version',
                        'id'=>'23',
                        'access_point'=>array(0=>array(
                            'access_point_type'=>'webPage',
                            'access_point_format'=>'XML',
                            'access_point_URI'=>'http://some.web.access/file'
                        ))
                    ),
                    2=>array('title'=>'this is an older version title',
                        'status'=>'depreciated',
                        'release_date'=>'01-03-2015',
                        'note'=>'Just a little bit more info on the version',
                        'id'=>'23',
                        'access_point'=>array(0=>array(
                            'access_point_type'=>'webPage',
                            'access_point_format'=>'XML',
                            'access_point_URI'=>'http://some.web.access/file'
                        )
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
     * Add a new vocabulary by
     * Creating a new _vocabulary object
     * Populate it with data
     * And then save it
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param $data data to save to the database
     */
    public function addNew($data) {
        $vocab = new _vocabulary();
        if(isset($data['id'])) unset($data['id']);
        $vocab->populate($data);
        try {
            $result = $vocab->save();
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete this vocabulary
     * - Remove all versions
     * - Delete the vocabulary
     * - Clear SOLR index of this record
     * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param int $id ID of the vocabulary for deletion
     * @return boolean 
     */
    public function delete($id) {
        $this->vocab_db = $this->load->database('vocabs', true);

        //delete all versions
        $this->vocab_db->delete('versions', array('vocab_id'=>$id));

        //delete the vocabulary
        $this->vocab_db->delete('vocabularies', array('id'=>$id));

        //clear SOLR index
        $this->load->library('solr');
        $vocab_config = get_config_item('vocab_config');
        if (!$vocab_config['solr_url']) throw new Exception('Indexer URL for Vocabulary module is not configured correctly');
        $this->solr->setUrl($vocab_config['solr_url']);

        $this->solr->deleteByID($id);
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