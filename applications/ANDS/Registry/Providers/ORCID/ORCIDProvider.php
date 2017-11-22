<?php
namespace ANDS\Registry\Providers\ORCID;
use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RegistryContentProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use DOMDocument;

/**
 * Class ORCIDProvider
 * @package ANDS\Registry\Providers\ORCID
 */
class ORCIDProvider implements RegistryContentProvider
{

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        // TODO: Implement get() method.
    }

    /**
     * Return the ORCID XML format for the provided record
     * TODO: replace XSLT with a DOM builder
     *
     * @param RegistryObject $record
     * @param ORCIDRecord $orcid
     * @return string
     */
    public static function getORCIDXML(RegistryObject $record, ORCIDRecord $orcid)
    {
        $data = MetadataProvider::getSelective($record, ['recordData']);
        $xml = $data['recordData'];

        // check if this is an update
        $existing = $orcid->exports->filter(function ($item) use ($record) {
            return $item->registry_object_id === $record->registry_object_id && $item->in_orcid;
        })->first();

        // TODO: description as attribute DescriptionProvider
        $processor = XMLUtil::getORCIDTransformer();
        $dom = new DOMDocument();
        $dom->loadXML($xml, LIBXML_NOENT);
        $processor->setParameter('','dateProvided', date("Y"));
        $processor->setParameter('','createdDate', DatesProvider::getPublicationDate($record, $data, 'Y'));
        $processor->setParameter('','rda_url', $record->portalUrl);
        $processor->setParameter('','rda_url_key', $record->portalUrlWithKey);
        $processor->setParameter('', 'title', $record->title);
        $processor->setParameter('', 'description', '');
        if ($existing) {
            $processor->setParameter('', 'put_code', $existing->put_code);
        }
        $xml = $processor->transformToXML($dom);
        return $xml;
    }
}