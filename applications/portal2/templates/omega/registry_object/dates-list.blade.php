@if(($ro->dates && isset($ro->dates[0]['type']))|| ($ro->temporal && isset($ro->temporal[0]['date'])))
<h2>Dates</h2>
@if($ro-dates)
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
@if($ro-temporal)
    @foreach($ro->temporal as $date)
    <p>
        Data Temporal Coverage
        <?php
        foreach($date['date'] as $each_date)
        {
            echo $each_date['type']." ".date('d M Y',strtotime($each_date['date']))." ";
        }
        ?>
    </p>
    @endforeach
@endif
@endif

