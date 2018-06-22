@if($ro->contact)

    <?php
    $order = array('electronic_email', 'electronic_url', 'electronic_other');
    $contactInfo = '';
    ?>
    @foreach($order as $o)
        @foreach($ro->contact as $contact)
        <?php
           if($contact['contact_type'] == $o){

            if($contact['contact_type']=='electronic_url'&& $contact['contact_value']!=''){
                $contactInfo .= '<a href="'.$contact['contact_value'].'">'.$contact['contact_value'].'</a><br/>';
            }
            else{
                $contactInfo .=  $contact['contact_value'].'<br/>';
            }

            }
        ?>
        @endforeach
    @endforeach
    @foreach($ro->contact as $contact)
        <?php
            if($contact['contact_type']=='telephoneNumber'&& $contact['contact_value']!='')
            {
                $contactInfo .=  'Ph: '.$contact['contact_value'].'<br/>';
            }
            elseif($contact['contact_type']=='faxNumber'&& $contact['contact_value']!='')
            {
                $contactInfo .=  'Fax: '.$contact['contact_value'].'<br/>';
            }
            elseif($contact['contact_value']!=''&& str_replace("electronic_","",$contact['contact_type']) == $contact['contact_type']){
                $contactInfo .= html_entity_decode($contact['contact_value'])."<br/>";
            }
            elseif(str_replace("electronic_","",$contact['contact_type']) != $contact['contact_type'] && !in_array($contact['contact_type'],$order)){
                $contactInfo .=  $contact['contact_value'].'<br/>';
            }
            elseif($contact['contact_type'] == "end") {
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