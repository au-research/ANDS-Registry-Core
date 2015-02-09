@if($ro->identifiers)
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <div class="panel-heading">
            <a href="">Identifiers</a>
        </div>
        <div class="panel-body swatch-white">
            <ul>
                @foreach($ro->identifiers as $col)
                    @if($col['identifier']['display_text'])
                        <?php
                        $col['type'] = $col['identifier']['display_text'];
                        ?>
                    @endif
                    <?php
                    echo '<li>' . $col['type'] . " : ";
                    if (isset($col['identifier']['href']) && $col['identifier']['href'] != '') {
                        echo '<a href="' . $col['identifier']['href'] . '">' . $col['value'] . '</a>';
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