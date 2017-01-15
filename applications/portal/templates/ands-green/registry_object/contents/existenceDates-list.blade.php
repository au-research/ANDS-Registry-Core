@if($ro->existenceDates)
    @foreach($ro->existenceDates as $date)
        <?php
        $date = str_replace("T00:00:00Z","",$date);
        echo $date;
        if(strlen($date)<11){
            echo " - ";
        }
        ?>
    @endforeach
@endif