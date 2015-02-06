@if($ro->relatedInfo)
<?php   $heading = '<div class="panel panel-primary panel-content swatch-white">
    <div class="panel-heading">Related Publications</div>
    <div class="panel-body">';
        $closing = '</div></div>';
        $found=false;
        ?>


        @foreach($ro->relatedInfo as $relatedInfo)
            @if($relatedInfo['type']=='publication')
                <?php
                if(!$found){
                    echo $heading;
                    $found = true;
                }?>
                <img src="<?php echo base_url()?>assets/core/images/icons/publications.png"   style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}<br/>
                {{$relatedInfo['identifier']['identifier_type']}} :
                @if($relatedInfo['identifier']['identifier_href'])
                    <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                @else
                    {{$relatedInfo['identifier']['identifier_value']}}<br />
                @endif
                @if($relatedInfo['relation']['url'])
                    URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a><br />
                @endif
            @endif
        @endforeach
    <?php if($found) echo $closing; ?>
@endif