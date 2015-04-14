@if($ro->contact)

<div id="contact">
    <h4>Contact Information</h4>
    @foreach($ro->contact as $contact)
    <?php
        if($contact['contact_type']=='url'){
            echo '<p><a href="'.$contact['contact_value'].'">'.$contact['contact_value'].'</a></p>';
        }
        if($contact['contact_type']=='email'){
            echo  '<p>'.$contact['contact_value'].'</p>';
        }
        elseif($contact['contact_type']=='telephoneNumber')
        {
            echo  '<p>Ph: '.$contact['contact_value'].'</p>';
        }
        elseif($contact['contact_type']=='faxNumber')
        {
            echo  '<p>Fax: '.$contact['contact_value'].'</p>';
        }
        else{
            echo html_entity_decode($contact['contact_value'])."<br />";
        }
    ?>
    @endforeach
</div>
@endif