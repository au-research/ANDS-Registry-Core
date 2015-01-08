@if($ro->rights)
<?php
$order = array('licence','rightsStatement','accessRights');
?>

<div id="rights">
    <h3>Licence & Rights</h3>

    @foreach($order as $o)
        @foreach($ro->rights as $right)
            @if($right['rights_type']==$o)
                <?php
                 switch($right['rights_type'])
                {
                   case 'licence':
                        echo "<h4>Licence</h4>";
                        break;
                   case 'accessRights':
                        echo "<h4>Access rights</h4>";
                        break;
                   case 'rightsStatement':
                        echo "<h4>Rights Statement</h4>";
                        break;
                   default;
                     break;
                 }
                ?>
                @if($right['uri'])
                {{$right['uri']}}<br />
                @endif
                @if($right['type'])
                {{$right['type']}}<br />
                @endif
                @if($right['value'])
                {{$right['value']}}<br />
                @endif
            @endif
        @endforeach
    @endforeach

</div>
@endif