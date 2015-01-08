@if($ro->identifiers)
    <h2>Identifiers</h2>
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
        @if($col['identifier']['display_icon'])
            {{$col['identifier']['display_icon']}}
        @endif
        <?php
        echo '</li>';
        ?>
    @endforeach
    </ul>
@endif