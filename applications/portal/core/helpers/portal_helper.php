<?php

use MinhD\SolrClient\SolrClient;

function dist_url($path)
{
    $manifestLocation = __DIR__. '/../assets/dist/manifest.json';

    if (!file_exists($manifestLocation)) {
        // TODO: manifest file cannot be found, log problem
        return "";
    }

    $manifest = json_decode(file_get_contents($manifestLocation), true);
    foreach ($manifest as $revision) {
        if ($revision['originalPath'] === $path) {
            return asset_url($revision['versionedPath'], 'dist');
        }
    }

    // TODO $path not found in manifest, log problem
    return "";
}

function subjectSortResolved($a, $b) {
    if ($a['resolved'] == $b['resolved']) {
        return 0;
    }
    return ($a['resolved'] < $b['resolved']) ? -1 : 1;
}

function class_name($text) {
	switch($text) {
		case 'collection': return 'Datasets'; break;
        case 'collection_data': return 'Data'; break;
        case 'collection_software':return 'Software'; break;
		case 'party': return 'People and Organisations'; break;
		case 'service': return 'Tools and Services'; break;
		case 'activity': return 'Grants and Projects'; break;
		default: return $text;
	}
}

function profile_image() {

	$ci =& get_instance();
	if ($ci->user->loggedIn()) {
		$role_db = $ci->load->database('roles', TRUE);
		$result = $role_db->get_where('roles', array('role_id'=>$ci->user->localIdentifier()));

		if($result == null)
			return false;

		if ($result->num_rows() > 0) {
			$r = $result->first_row();
			if ($r->oauth_data) {
				$data = json_decode($r->oauth_data, true);
				if ($data['photoURL']) return $data['photoURL'];
			} else {
				return asset_url('images/generic_user.png', 'core');
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}



?>