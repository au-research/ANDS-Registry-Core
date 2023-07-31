@if($ro->ARDC_campaign && \ANDS\Util\config::get('app.ardc_campaign')=="TRUE")
    <div class="padding-bottom">
        <a href="{{$ro->ARDC_campaign['link']}}"  style="margin-right:5px;">
            <img src="{{$ro->ARDC_campaign['image']}}"/>
        </a>
    </div>
@endif