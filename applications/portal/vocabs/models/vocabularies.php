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
				'title' => 'ANZSRC-FOR',
				'slug' => 'anzsrc-for',
				'vocab_uri' => 'http://vocabs.ands.org.au/anzsrc-for'
			)
		);

		$test_vocab2 = new _vocabulary();
		$test_vocab2->populate(
			array (
				'title' => 'ANZSRC-SEO',
				'slug' => 'anzsrc-seo',
				'vocab_uri' => 'http://vocabs.ands.org.au/anzsrc-seo'
			)
		);

		$test_records = array($test_vocab1, $test_vocab2);
		return $test_records;
	}

	function __construct() {
		parent::__construct();
		include_once("_vocabulary.php");
	}
}