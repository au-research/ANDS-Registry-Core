<?php

namespace ANDS\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class DOIAPI
{
    /**
     * Resolve a DOI String
     *
     *
     * @param $doi
     * @return array|null
     */
    public static function resolve($doi)
    {
        $result = self::resolveDOIContentNegotiation($doi);

        if ($result == null) {
            return null;
        }

        $result = json_decode($result, true);

        return [
            'title' => array_key_exists('title', $result) ? $result['title'] : "No Title",
            'publisher' => array_key_exists('publisher', $result) ? $result['publisher'] : "Unknown Publisher",
            'source' => array_key_exists('source', $result) ? $result['source'] : "",
            'DOI' => array_key_exists('DOI', $result) ? $result['DOI'] : $doi,
            'type' => array_key_exists('type', $result) ? $result['type'] : 'Unknown Type',
            'url' => array_key_exists('URL', $result) ? $result['URL'] : "https://doi.org/$doi",
            'description' => array_key_exists('abstract', $result) ? $result['abstract'] : ""
        ];
    }

    /**
     * Resolve DOI via the Content Negotiation API
     *
     * @see https://support.datacite.org/docs/datacite-content-resolver
     * @param $doi
     * @param $format
     * @return mixed|null
     */
    public static function resolveDOIContentNegotiation($doi, $format = "application/vnd.citationstyles.csl+json, application/json")
    {
        $client = new Client([
            'base_uri' => 'https://doi.org/',
            'time_out' => 10,
            'headers' => [
                'Accept' => $format
            ],
        ]);
        try {
            $data = $client->get($doi);

            // todo check matching Content-Type, some DOI resolution returns text/html

            return $data->getBody()->getContents();
        } catch (ClientException $e) {
            // todo log error
            return null;
        }

    }
}