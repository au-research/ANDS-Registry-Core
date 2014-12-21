@if($ro->connectiontree[0]['children'])

<h2>This dataset is part of a larger collection </h2>

<?php
$tree = recurse($ro->connectiontree, $parent = null, $level = 0);
echo $tree;
?>
@endif

<?php

function recurse($collectiontree, $parent = null, $level = 0)
{
    $ret = '<ul>';
    foreach($collectiontree as  $collection)
    {
        if(count($collection['children'])>0){
            foreach($collection['children'] as $child){
            $ret .= '<li><a href="#"><p class="Tier' . $level . '">' . $child['title'] . '</p></a>';
            $sub = recurse($collection['children'], null, $level+1);
            if($sub != '<ul></ul>')
                $ret .= $sub;
            $ret .= '</li>';

        }
    }
}

    return $ret . '</ul>';
   // echo '</ul>';
}

?>

