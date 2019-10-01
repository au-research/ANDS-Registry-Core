<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 23/9/19
 * Time: 2:20 PM
 */

namespace ANDS\Registry\ContentProvider\ISO;
use ANDS\Registry\ContentProvider\MetadataContentProvider;
use Exception;
use DOMDocument;
use ANDS\Registry\ContentProvider\ISO\IdentifierProvider;

class ISO191153ContentProvider extends MetadataContentProvider
{
    protected $fileExtension = 'tmp';
    protected $payloadCounter = 0;
    protected $content = [];
    protected $errors = [];

    public function init()
    {
        $this->content = [];
        return $this;
    }

    public function loadContent($fileContent)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $this->content = [];
        try{
            $dom->loadXML($fileContent);
            $mdNodes = $dom->documentElement->getElementsByTagName('MD_Metadata');
            $errors = libxml_get_errors();
            if($errors) {
                foreach ($errors as $error) {
                    $this->add_load_error($error, $fileContent);
                }
            }
            else{
                foreach ($mdNodes as $mdNode) {
                    $record['identifiers'] = IdentifierProvider::getIdentifier($mdNode);
                    $record['nameSpaceURI'] = $mdNode->namespaceURI;
                    $dom = new DomDocument('1.0', 'UTF-8');
                    $dom->appendChild($dom->importNode($mdNode, True));
                    $record['data'] = $dom->saveXML($dom->documentElement);
                    $record['hash'] = md5($record['data']);
                    $this->content[] = $record;
                    $this->payloadCounter++;
                }
            }
            libxml_clear_errors();
        }
        catch(Exception $e){
            throw new Exception("Errors while loading  ISO Content Error message:". $e->getMessage());
        }
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    function add_load_error($error, $xml)
    {
        $error_msg  = $xml[$error->line - 1] . "\n";
        $error_msg.= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $error_msg .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $error_msg .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $error_msg .= "Fatal Error $error->code: ";
                break;
        }

        $error_msg .= trim($error->message) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        if ($error->file) {
            $error_msg .= "\n  File: $error->file";
        }

        $this->errors[] = "Errors while loading  content Error message:". $error_msg;
    }

}