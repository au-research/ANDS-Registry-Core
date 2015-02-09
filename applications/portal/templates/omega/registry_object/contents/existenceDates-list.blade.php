@if($ro->existenceDates)
    @foreach($ro->existenceDates as $date)
        {{$date}}
    @endforeach
@endif