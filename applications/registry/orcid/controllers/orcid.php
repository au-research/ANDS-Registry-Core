<?php

use ANDS\Authenticator\ORCIDAuthenticator;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ORCID base controller for the orcid integration process
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au> 
 */
class Orcid extends MX_Controller {

	/**
	 * Base Method, requires the user to login
	 * @return void
	 */
	function index()
    {
        if (!ORCIDAuthenticator::isLoggedIn()) {
            redirect(registry_url('orcid/login'));
        }

        // is logged in, obtain orcid record and open the wizard
        $orcid = ORCIDAuthenticator::getSession();
        $this->wiz($orcid);
    }

    /**
     * Login view
     * The link is provided by ORCIDAuthenticator
     */
    public function login()
    {
        $this->load->view('login_orcid', [
            'title' => 'Login to ORCID',
            'js_lib' => ['core'],
            'link' => ORCIDAuthenticator::getOauthLink(registry_url('orcid/auth'))
        ]);
    }

    /**
     * Logout
     * Redirects to Login when done
     */
    public function logout()
    {
        ORCIDAuthenticator::destroySession();
        redirect(registry_url('orcid/login'));
    }

    /**
     * REDIRECT URI set to this method, process the user and provide the relevant view
     * @return void
     * @throws Exception
     */
	public function auth() {

	    // see if there's any error
        if (Request::get('error')) {

            // user deny the auth
            if (Request::get('error') == "access_denied") {
                redirect(registry_url('orcid/login'));
            }

            throw new Exception(Request::get('error_description'));
        }

        if (ORCIDAuthenticator::isLoggedIn()) {
            $orcid = ORCIDAuthenticator::getSession();
            $this->wiz($orcid);
            return;
        }

        if (!Request::get('code')) {
            // require the user to login via ORCID
            redirect(ORCIDAuthenticator::getOauthLink());
            throw new Exception("No valid code returned from ORCID");
        }

        // code authentication as per normal
        $orcid = ORCIDAuthenticator::oauth(Request::get('code'));
        $this->wiz($orcid);
        return;
	}

    /**
     * The wizard view
     * @param ORCIDRecord $orcid
     */
	function wiz(ORCIDRecord $orcid) {
	    $this->load->view('orcid_app', [
            'title' => 'Research Data Australia - Link Your Work to your ORCID Record',
            'scripts' => ['orcid_app'],
            'js_lib' => ['core','prettyprint', 'angular'],
            'orcid' => $orcid
        ]);
	}
}
	