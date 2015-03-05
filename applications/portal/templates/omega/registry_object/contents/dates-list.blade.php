@if(($ro->dates && isset($ro->dates[0]['type']))|| ($ro->temporal && isset($ro->temporal[0]['date'])))
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <div class="panel-heading">
            <a href="">Dates</a>
        </div>
        <div class="panel-body swatch-white">

            @if($ro->dates)
                @foreach($ro->dates as $date)
                <p>
                    {{$date['displayType']}}:
                    <?php
                    $prev_date=Array();
                    foreach($date['date'] as $each_date)
                    {

                        if(isset($prev_date['type'])&& $prev_date['type']=='dateFrom' && $each_date['type']=='dateTo') $type = 'to';
                        elseif(isset($prev_date['type']) && $prev_date['type']=='dateTo'&& $each_date['type']=='dateTo') $type = ' ,';
                        else $type = '';
                        $prev_date=$each_date;
                        echo $type." ".date('d M Y',strtotime($each_date['date']))." ";
                    }
                    $prev_date='';
                    ?>
                </p>
                @endforeach

            @endif
            @if($ro->temporal)
                @foreach($ro->temporal as $date)
                <p>

                    Data Temporal Coverage:
                    <?php

                    if($date['type']=='date'){

                        foreach($date['date'] as $each_date)
                        {
                            if(isset($prev_date['type'])&& $prev_date['type']=='dateFrom' && $each_date['type']=='dateTo') $type = 'to';
                            elseif(isset($prev_date['type']) && $prev_date['type']=='dateTo'&& $each_date['type']=='dateTo') $type = ' ,';
                            else $type = '';
                            $prev_date=$each_date;
                            echo $type.' <span itemprop="temporal">'.date('d M Y',strtotime($each_date['date']))."</span> ";
                        }
                    } elseif ($date['type']=='text'){
                        echo (string)$date['date'];
                    }
                    ?>
                </p>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif