@if($ro->contact)

    <?php
    $order = array('electronic_email', 'electronic_url', 'electronic_other');
    $contactInfo = '';
    $streetAddress = false;
    $streetAddressPrev = 0;
    $postalAddress = false;
    $postalAddressPrev = 0;
    ?>

    @foreach($ro->contact as $contact)
        <?php
               // print_r($contact);
        //let's print out the postal address first if it exists - need to print them out in separate blocks if more than i exists
        if(str_replace("postalAddress", "", $contact['contact_type'])!=$contact['contact_type'] && $contact['contact_value']!='')
        {
            $getNum = explode("_",$contact['contact_type']);
            $postalAddress = $getNum[1];
            if($postalAddress > $postalAddressPrev) {
                if($postalAddressPrev > 0) $contactInfo .= "<br/>";
               $contactInfo .= "Postal Address: <br/>";
                $postalAddressPrev = $postalAddress;
           }
            $contactInfo .= $contact['contact_value'].'<br/>';
        }
        ?>
    @endforeach


    @foreach($ro->contact as $contact)
        <?php
        //lets print out the street address if it exists - need to print them out in separate blocks if more than i exists
        if(str_replace("streetAddress", "", $contact['contact_type'])!=$contact['contact_type'] && $contact['contact_value']!='')
        {
            $getNum = explode("_",$contact['contact_type']);
            $streetAddress = $getNum[1];
            if($streetAddress > $streetAddressPrev) {
                if($postalAddress || $streetAddressPrev > 0) $contactInfo .= "<br/>";
                $contactInfo .= "Street Address: <br/>";
                $streetAddressPrev = $streetAddress;
            }
            $contactInfo .= $contact['contact_value'].'<br/>';
        }
        ?>
    @endforeach

    <?php if($postalAddress || $streetAddress) $contactInfo .= "<br />"; ?>

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
            elseif($contact['contact_value']!=''
                    && str_replace("electronic_","",$contact['contact_type']) == $contact['contact_type']
                    && str_replace("streetAddress","",$contact['contact_type']) == $contact['contact_type']
                    && str_replace("postalAddress","",$contact['contact_type']) == $contact['contact_type']){
                $contactInfo .= html_entity_decode($contact['contact_value'])."<br/>";
            }
            elseif(str_replace("electronic_","",$contact['contact_type']) != $contact['contact_type'] && !in_array($contact['contact_type'],$order)){
                $contactInfo .=  $contact['contact_value'].'<br/>';
            }
            elseif($contact['contact_type'] == "end" && substr($contactInfo,-15)!= "<br/><br/><br/>") {
                $contactInfo .="<br/>";
            }
        ?>
    @endforeach
    <?php $contactInfo = str_replace("<br/><br/><br/>","<br/><br/>",$contactInfo); ?>
    @if(trim($contactInfo)!='' && trim($contactInfo)!='<br/>')
    <div id="contact">
        <h4>Contact Information</h4>
        {{$contactInfo}}
    </div>

    @endif
@endif