<?php

$cc=$vocab->licence;
?>
<style>
    .img-cc {
        height: 22px;
    }
    .img-cc-standard3 {
        width: auto;
        height: 48px;
    }
</style>
<div class="panel panel-primary swatch-white gray-bg">
    <div class="panel-body">

        <a href="{{$vocab->vocab_uri}}"  class="btn btn-lg btn-primary btn-block"><i class="fa fa-cube"></i> Go to Vocab</a>

    	<div class="btn-group btn-group-justified element element-shorter-bottom element-no-top" role="group" aria-label="...">
            <!--<a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="ro.bookmarked"><i class="fa fa-bookmark"></i> Saved to MyVocabs</a> -->
            <a class="btn btn-sm btn-default" ng-click="bookmark()" ng-if="!ro.bookmarked"><i class="fa fa-bookmark-o"></i> Save to MyVocabs</a>
        </div>

        <div>

        <h4>Licence</h4>
        @if($cc=='CC-BY')
            <a href="http://creativecommons.org/licenses/by/3.0/au/" tip="Attribution"><img src="{{asset_url('images/icons/CC-BY.png', 'core')}}" class="img-cc" alt="CC-BY"></a> <br/>
            @elseif($cc=='CC-BY-SA')
            <a href="http://creativecommons.org/licenses/by-sa/3.0/au/" tip="Attribution-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-SA.png', 'core')}}" class="img-cc" alt="CC-BY-SA"></a> <br/>
            @elseif($cc=='CC-BY-ND')
            <a href="http://creativecommons.org/licenses/by-nd/3.0/au/" tip="Attribution-No Derivatives"><img src="{{asset_url('images/icons/CC-BY-ND.png', 'core')}}" class="img-cc" alt="CC-BY-ND"></a> <br/>
            @elseif($cc=='CC-BY-NC')
            <a href="http://creativecommons.org/licenses/by-nc/3.0/au/" tip="Attribution-Non Commercial"><img src="{{asset_url('images/icons/CC-BY-NC.png', 'core')}}" class="img-cc" alt="CC-BY-NC"></a> <br/>
            @elseif($cc=='CC-BY-NC-SA')
            <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/au/" tip="Attribution-Non Commercial-Shared Alike"><img src="{{asset_url('images/icons/CC-BY-NC-SA.png', 'core')}}" class="img-cc" alt="CC-BY-NC-SA"></a> <br/>
            @elseif($cc=='CC-BY-NC-ND')
            <a href="http://creativecommons.org/licenses/by-nc-nd/3.0/au/" tip="Attribution-Non Commercial-Non Derivatives"><img src="{{asset_url('images/icons/CC-BY-NC-ND.png', 'core')}}" class="img-cc" alt="CC-BY-NC-ND"></a> <br/>
            @else
            <span>{{sentenceCase($cc)}}</span>
        @endif

        </div>


            <div id="rightsContent">
                where does the text come from for this info block
            </div>




    </div>
</div>