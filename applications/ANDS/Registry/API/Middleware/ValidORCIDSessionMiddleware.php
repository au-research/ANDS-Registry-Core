<?php
namespace ANDS\Registry\API\Middleware;


use ANDS\Authenticator\ORCIDAuthenticator;

class ValidORCIDSessionMiddleware extends Middleware
{
    public function pass()
    {
        if (!ORCIDAuthenticator::isLoggedIn()) {
            throw new \Exception("User is not logged in to ORCID");
        }
        return true;
    }
}