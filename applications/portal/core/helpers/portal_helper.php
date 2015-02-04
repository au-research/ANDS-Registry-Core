<?php
function subjectSortResolved($a, $b) {
    if ($a['resolved'] == $b['resolved']) {
        return 0;
    }
    return ($a['resolved'] < $b['resolved']) ? -1 : 1;
}
?>