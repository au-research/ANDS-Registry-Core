@if($ro->contact)
    <?php
    $contactInfo = '';
    ?>
    @foreach($ro->contact as $contact)
    <?php
        if($contact['contact_type']=='url'&& $contact['contact_value']!=''){
            $contactInfo .= '<p><a href="'.$contact['contact_value'].'">'.$contact['contact_value'].'</a></p>';
        }
        if($contact['contact_type']=='email'&& $contact['contact_value']!=''){
            $contactInfo .=  '<p>'.$contact['contact_value'].'</p>';
        }
        elseif($contact['contact_type']=='telephoneNumber'&& $contact['contact_value']!='')
        {
            $contactInfo .=  '<p>Ph: '.$contact['contact_value'].'</p>';
        }
        elseif($contact['contact_type']=='faxNumber'&& $contact['contact_value']!='')
        {
            $contactInfo .=  '<p>Fax: '.$contact['contact_value'].'</p>';
        }
        elseif($contact['contact_value']!=''){
            $contactInfo .= html_entity_decode($contact['contact_value'])."<br />";
        }
    ?>
    @endforeach

    @if(trim($contactInfo)!='')
    <div id="contact">
        <h4>Contact Information</h4>
        {{$contactInfo}}
    </div>

    @endif
@endif