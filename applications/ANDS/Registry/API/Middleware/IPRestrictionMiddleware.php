<?php
namespace ANDS\Registry\API\Middleware;

use ANDS\Registry\API\Request;
use ANDS\Util\Config;
use ANDS\Util\IPValidator;
use \Exception as Exception;

class IPRestrictionMiddleware extends Middleware
{
    /**
     * @return bool
     * @throws Exception
     */
    public function pass()
    {
        $whitelist = Config::get('app.api_whitelist_ip');
        if (!$whitelist) {
            throw new Exception("Whitelist IP not configured properly. This operation is unsafe.");
        }
        $ip = Request::ip();

        if (!IPValidator::validate($ip, $whitelist)) {
            throw new Exception("IP: $ip is not whitelisted for this behavior");
        }

        return true;
    }
}