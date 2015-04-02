@if($ro->relatedInfo)

<?php
    //prepare
    $found = false;
    foreach($ro->relatedInfo as $relatedInfo) {
        if ($relatedInfo['type']=='publication') {
            $found = true;
        }
    }
?>

<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        @if($found)
        <!-- <div class="panel-heading"> <a href="">Related Publications</a> </div> -->
        @endif
        <div class="panel-body swatch-white">
            @foreach($ro->relatedInfo as $relatedInfo)
                @if(@if($relatedInfo['identifier']['identifier_type'] == 'doi)
                    <h5><a href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}"><img src="<?php echo base_url()?>assets/core/images/icons/publications.png" style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}</a></h5>
                @else
                <h5><img src="<?php echo base_url()?>assets/core/images/icons/publications.png" style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}</a></h5>
                @endif
                <p>
                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b> : 
                    @if($relatedInfo['identifier']['identifier_href'])
                        <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                    @else
                        {{$relatedInfo['identifier']['identifier_value']}}
                    @endif
                </p>
                @if($relatedInfo['relation']['url'])
                    <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
                @endif
            @endforeach
        </div>
    </div>
</div>

@endif