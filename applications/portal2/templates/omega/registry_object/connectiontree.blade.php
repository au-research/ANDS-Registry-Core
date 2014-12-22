@if($ro->connectiontree[0]['children'])

<h2>This dataset is part of a larger collection </h2>
<?php $data = urlencode(json_encode($ro->connectiontree)); ?>
<div id="connectionTree" mydata="<?=$data?>" ro_id="<?=$ro->id?>">
<?php
//$tree = recurse($ro->connectiontree, $parent = null, $level = 0);
//echo $tree;
?>
@endif

<?php

function recurse($collectiontree, $parent = null, $level = 0)
{
    $ret = '<ul class="<dynatree-container>">';
    foreach($collectiontree as  $collection)
    {
        $ret .=  '<li class="dynattree-lastsib"><span class="dynatree-node dynatree-expanded dynatree-has-children dynatree-lastsib dynatree-exp-el dynatree-ico-e title="' . $collection['title'] . '>';
        $ret .= '<span class="dynatree-expander"></span>';
        $ret .= '<span class="dynatree-icon style="background-position: -38px -155px;"></span>';
        $ret .= '<a class="dynatree-title">' . $collection['title'] . '</a></span>';
        if(count($collection['children'])>0){
            $level++;
            foreach($collection['children'] as $child){
                $sub = recurse($collection['children'], null, $level);
                if($sub != '<ul></ul>')
                $ret .= $sub;
            }
        $ret .= '</li>';
        }
    }

    return $ret . '</ul>';
}

?>
</div>
