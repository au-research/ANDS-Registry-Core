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
                    {{$date['displayType']}}
                    <?php
                    foreach($date['date'] as $each_date)
                    {
                      echo $each_date['type']." ".date('d M Y',strtotime($each_date['date']))." ";
                    }
                    ?>
                </p>
                @endforeach
            @endif
            @if($ro->temporal)
                @foreach($ro->temporal as $date)
                <p>

                    Data Temporal Coverage
                    <?php

                    if($date['type']=='date'){

                        foreach($date['date'] as $each_date)
                        {
                            echo $each_date['type']." ".date('d M Y',strtotime($each_date['date']))." ";
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