@if($ro->identifiers)
<div class="swatch-white">
    <div class="panel element-no-top element-short-bottom">
        <div class="panel-heading"> Identifiers </div>
        <div class="panel-body swatch-white">
            <ul class="list-unstyled">
                @foreach($ro->identifiers as $col)
                    <?php $hover_text=''; ?>
                    @if(isset($col['identifier']['display_text']))
                        <?php
                        $col['type'] = $col['identifier']['display_text'];
                        ?>
                    @endif
                    @if(isset($col['identifier']['hover_text']))
                    <?php
                        $hover_text=$col['identifier']['hover_text'];
                    ?>
                    @endif
                    <?php
                    echo '<li>' . $col['type'] . " : ";
                    $itemprop ='';
                    if($col['type']='doi'||$col['type']=='purl'||$col['type']=='uri'||$col['type']=='handle'||$col['type']=='url') $itemprop = 'itemprop="url"';
                    if (isset($col['identifier']['href']) && $col['identifier']['href'] != '') {
                        echo '<a href="' . $col['identifier']['href'] . '" '.$itemprop.' title="'.$hover_text.'">' . $col['value'] . '</a>';
                    } else {
                        echo $col['value'];
                    }
                    ?>
                    @if(isset($col['identifier']['display_icon']))
                        {{$col['identifier']['display_icon']}}
                    @endif
                    <?php
                    echo '</li>';
                    ?>
                @endforeach
                </ul>
        </div>
    </div>
</div>
@endif