@if($ro->contact)
    <?php
    $contactInfo = '';
    ?>
    @foreach($ro->contact as $contact)
    <?php
        if($contact['contact_type']=='url'&& $contact['contact_value']!=''){
            $contactInfo .= '<a href="'.$contact['contact_value'].'">'.$contact['contact_value'].'</a><br/>';
        }
        if($contact['contact_type']=='email'&& $contact['contact_value']!=''){
            $contactInfo .=  $contact['contact_value'].'<br/>';
        }
        elseif($contact['contact_type']=='telephoneNumber'&& $contact['contact_value']!='')
        {
            $contactInfo .=  'Ph: '.$contact['contact_value'].'<br/>';
        }
        elseif($contact['contact_type']=='faxNumber'&& $contact['contact_value']!='')
        {
            $contactInfo .=  'Fax: '.$contact['contact_value'].'<br/>';
        }
        elseif($contact['contact_value']!=''){
            $contactInfo .= html_entity_decode($contact['contact_value'])."<br/>";
        } elseif($contact['contact_type'] == "end") {
            $contactInfo .="<br/>";
        }
    ?>
    @endforeach

    @if(trim($contactInfo)!='' && trim($contactInfo)!='<br/>')
    <div id="contact">
        <h4>Contact Information</h4>
        {{$contactInfo}}
    </div>

    @endif
@endif