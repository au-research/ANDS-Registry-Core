<?php

namespace ANDS\DOI\Formatter;

class XMLFormatter extends Formatter
{
    /**
     * Format and return the payload
     *
     * @param $payload
     * @return string
     */
    public function format($payload)
    {
        $payload = $this->fill($payload);
        $str = "";
        $str .= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
        $str .="<response type=\"".$payload['type']."\">";
        $str .="<responsecode>".$payload['responsecode']."</responsecode>";
        $str .="<message>".$payload['message']."</message>";
        $str .="<doi>".$payload['doi']."</doi>";
        $str .="<url>".$payload['url']."</url>";
        $str .="<app_id>".$payload['app_id']."</app_id>";
        $str .="<verbosemessage>".$payload['verbosemessage']."</verbosemessage>";
        $str .="</response>";
        return $str;
    }

}