<?php

if (!function_exists('env')) {
    function env($env, $default = "")
    {
        return getenv($env) ?: $default;
    }
}

if (!function_exists('baseUrl')) {
    function baseUrl($suffix = "")
    {
        return env('PROTOCOL') . env('BASE_URL') . '/'. $suffix;
    }
}

/**
 * Returns the reverse form of a relationship
 *
 * @param $relation
 * @return string
 */
if (!function_exists('getReverseRelationshipString')) {
    function getReverseRelationshipString($relation)
    {
        switch ($relation) {
            case "describes" :
                return "isDescribedBy";
                break;
            case "isDescribedBy" :
                return "describes";
                break;
            case "hasPart" :
                return "isPartOf";
                break;
            case "isPartOf" :
                return "hasPart";
                break;
            case "hasAssociationWith" :
                return "hasAssociationWith";
                break;
            case "hasCollector" :
                return "isCollectorOf";
                break;
            case "isCollectorOf" :
                return "hasCollector";
                break;
            case "hasPrincipalInvestigator" :
                return "isPrincipalInvestigatorOf";
                break;
            case "isPrincipalInvestigatorOf" :
                return "hasPrincipalInvestigator";
                break;
            case "isLocatedIn" :
                return "isLocationFor";
                break;
            case "isLocationFor" :
                return "isLocatedIn";
                break;
            case "manages":
                return "isManagedBy";
                break;
            case "isManagedBy" :
                return "isManagerOf";
                break;
            case "isManagerOf" :
                return "isManagedBy";
                break;
            case "isOutputOf" :
                return "hasOutput";
                break;
            case "hasOutput" :
                return "isOutputOf";
                break;
            case "isOwnedBy" :
                return "isOwnerOf";
                break;
            case "isOwnerOf" :
                return "isOwnedBy";
                break;
            case "supports" :
                return "isSupportedBy";
                break;
            case "isSupportedBy" :
                return "supports";
                break;
            case "isEnrichedBy" :
                return "enriches";
                break;
            case "enriches" :
                return "isEnrichedBy";
                break;
            case "isDerivedFrom" :
                return "hasDerivedCollection";
                break;
            case "hasDerivedCollection" :
                return "isDerivedFrom";
                break;
            case "isAvailableThrough" :
                return "makesAvailable";
                break;
            case "makesAvailable" :
                return "isAvailableThrough";
                break;
            case "isProducedBy" :
                return "produces";
                break;
            case "produces" :
                return "isProducedBy";
                break;
            case "isPresentedBy" :
                return "presents";
                break;
            case "presents" :
                return "isPresentedBy";
                break;
            case "hasValueAddedBy" :
                return "addsValueTo";
                break;
            case "addsValueTo" :
                return "hasValueAddedBy";
                break;
            case "isOperatedOnBy" :
                return "operatesOn";
                break;
            case "operatesOn" :
                return "isOperatedOnBy";
                break;
            case "isCitedBy" :
                return "cites";
                break;
            case "cites" :
                return "isCitedBy";
                break;
            case "isReferencedBy" :
                return "references";
                break;
            case "references" :
                return "isReferencedBy";
                break;
            case "isDocumentedBy" :
                return "documents";
                break;
            case "documents" :
                return "isDocumentedBy";
                break;
            case "isSupplementedBy" :
                return "isSupplementTo";
                break;
            case "isSupplementTo" :
                return "isSupplementedBy";
                break;
            case "hasParticipant" :
                return "isParticipantIn";
                break;
            case "isParticipantIn" :
                return "hasParticipant";
                break;
            case "isFundedBy" :
                return "isFunderOf";
                break;
            case "isFunderOf" :
                return "isFundedBy";
                break;
            case "isMemberOf" :
                return "hasMember";
                break;
            case "hasMember" :
                return "isMemberOf";
                break;
            case "isReviewedBy" :
                return "reviews";
                break;
            case "reviews" :
                return "isReviewedBy";
                break;
        }
        return $relation;
    }
}