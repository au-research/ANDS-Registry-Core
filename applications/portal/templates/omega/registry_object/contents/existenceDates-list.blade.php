@if($ro->existenceDates)
    @foreach($ro->existenceDates as $date)
        {{str_replace("T00:00:00Z","",$date)}}
    @endforeach
@endif