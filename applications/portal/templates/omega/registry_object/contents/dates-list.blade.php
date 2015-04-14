@if(($ro->dates && isset($ro->dates[0]['type']))|| ($ro->temporal && isset($ro->temporal[0]['date'])))
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <!-- <div class="panel-heading"> Dates </div> -->
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
                        echo $type." ".nicifyDate($each_date['date'])." ";
                    }
                    $prev_date='';
                    ?>
                </p>
                @endforeach

            @endif
            @if($ro->temporal)
                @foreach($ro->temporal as $date)
                <p>

                    Data time period:
                    <?php

                    if($date['type']=='date'){

                        $prev_date=Array();
                        foreach($date['date'] as $each_date)
                        {

                            if(isset($prev_date['type'])&& $prev_date['type']=='dateFrom' && $each_date['type']=='dateTo') $type = 'to';
                            elseif(isset($prev_date['type']) && $prev_date['type']=='dateTo'&& $each_date['type']=='dateTo') $type = ' ,';
                            elseif(isset($prev_date['type']) && $prev_date['type']=='dateTo'&& $each_date['type']=='dateFrom') $type = ' ,';
                            else $type = '';
                            $prev_date=$each_date;
                            echo $type." ".nicifyDate($each_date['date'])." ";
                        }
                        $prev_date='';
                    } elseif ($date['type']=='text'){
                        foreach($date['date'] as $each_date)
                        {
                            echo $each_date['date']."<br />";
                        }
                    }
                    ?>
                </p>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif