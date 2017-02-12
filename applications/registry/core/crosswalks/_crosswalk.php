<?php

abstract class Crosswalk
{
    // Force Extending class to define this method
    abstract public function identify();
    abstract public function payloadToRIFCS($payload);
    abstract public function validate($payload);
    abstract public function metadataFormat();
}