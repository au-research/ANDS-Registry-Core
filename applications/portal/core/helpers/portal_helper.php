<?php
function subjectSortResolved($a, $b) {
    if ($a['resolved'] == $b['resolved']) {
        return 0;
    }
    return ($a['resolved'] < $b['resolved']) ? -1 : 1;
}

function class_name($text) {
	switch($text) {
		case 'collection': return 'Datasets'; break;
		case 'party': 'return People and Organisations'; break;
		case 'service': return 'Tools and Services'; break;
		case 'activity': return 'Projects'; break;
		default: return $text;
	}
}

?>