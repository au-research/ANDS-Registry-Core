<?php

namespace ANDS\OAI\Interfaces;

interface OAIRepository
{
    public function identify();
    public function listSets($limit = 0, $offset = 0);
    public function listSetsByToken($token);
    public function getRecord($metadataFormat, $identifier);
    public function listRecords($options);
    public function listRecordsByToken($token);
    public function listMetadataFormats($identifier = null);
    public function listIdentifiers($options);

    // helper
    public function getDateFormat();
}