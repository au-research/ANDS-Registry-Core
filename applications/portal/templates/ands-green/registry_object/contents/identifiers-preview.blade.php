@if($ro->identifiers)


        <div id=identifiers"><h4> Identifiers </h4></div>

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
                    if (isset($col['identifier']['href']) && $col['identifier']['href'] != '') {
                        echo '<a href="' . $col['identifier']['href'] . '" title="'.$hover_text.'" target="_blank">' . $col['value'] . '</a>';
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

@endif