@if($ro->identifiers)
<h2>Identifiers</h2>
<ul>
	@foreach($ro->identifiers as $col)
    <?php
        echo '<li>'.$col['type']. " : " ;
        if(isset($col['identifier']['href']) && $col['identifier']['href']!='') {
            echo '<a href="'.$col['identifier']['href'].'">'.$col['value'].'</a>';
        }else{
            echo $col['value'];
        }
        echo "</li>"
    ?>
	@endforeach
</ul>
@endif