<?php
function authenticateWithLDAP($role_id, $passphrase, &$LDAPAttributes, &$userMessage)
{


		$eLDAPHost = gCOSI_AUTH_LDAP_HOST;//"ldap://ldap.anu.edu.au";
		$eLDAPPort = gCOSI_AUTH_LDAP_PORT;//389; //636 | 389
		$eLDAPBaseDN = gCOSI_AUTH_LDAP_BASE_DN;//"ou=People, o=anu.edu.au";
		$eLDAPuid = gCOSI_AUTH_LDAP_UID;//"uid=@@ROLE_ID@@";
		$eLDAPDN = gCOSI_AUTH_LDAP_DN;//"$eLDAPuid, $eLDAPBaseDN";
		
		$validCredentials = false;
		
		if( $eLDAPBaseDN && $eLDAPuid )
		{
			$ldapDN = str_replace("@@ROLE_ID@@", escLDAPChars($role_id), $eLDAPDN);
			$ldapconn = ldap_connect($eLDAPHost, $eLDAPPort);
		
			if( $ldapconn && $passphrase != '' )
			{
				// Suppress warnings when binding to the LDAP server
				$ldapbind = @ldap_bind($ldapconn, $ldapDN, $passphrase);

				if( $ldapbind )
				{
					$validCredentials = true;
				
					// Put this user's LDAP attributes into session to make them available
					// for use with authorisations and stuff.
					$ldapUserDN = str_replace("@@ROLE_ID@@", escLDAPChars($role_id), $eLDAPuid);
					$searchResult = ldap_search($ldapconn, $eLDAPBaseDN, $ldapUserDN);
					if( $searchResult && ldap_count_entries($ldapconn, $searchResult) === 1 )
					{
						$entry = ldap_first_entry($ldapconn, $searchResult);
						$LDAPAttributes = ldap_get_attributes($ldapconn, $entry);
					}
				
					ldap_unbind($ldapconn);
				}
				else
				{
					$ldapErrorNumber = ldap_errno($ldapconn);
					if( $ldapErrorNumber === 49 ) // 0x31 = 49 is the LDAP error number for invalid credentials.
					{
						$userMessage = "LOGIN FAILED\nInvalid user ID/password [31,49].\n";
					}
					else
					{
						$userMessage = "LOGIN FAILED\nAuthentication service error [32,$ldapErrorNumber].\n";
					}
					/* 
					LDAP error numbers have the same meaning across implementations, though the messages vary.
	 
					A list of implementation specific error messages can be obtained using:
					 	for ($i=-1; $i<100; $i++) {
					 		printf("Error $i: %s<br />\n", ldap_err2str($i));
					 	}
	
					Error numbers and messages are for troubleshooting, and should not be displayed to users.
	
					Example results:
						Error -1: Can't contact LDAP server
						Error 0: Success
						Error 1: Operations error
						Error 2: Protocol error
						Error 3: Time limit exceeded
						Error 4: Size limit exceeded
						Error 5: Compare False
						Error 6: Compare True
						Error 7: Authentication method not supported
						Error 8: Strong(er) authentication required
						Error 9: Partial results and referral received
						Error 10: Referral
						Error 11: Administrative limit exceeded
						Error 12: Critical extension is unavailable
						Error 13: Confidentiality required
						Error 14: SASL bind in progress
						Error 16: No such attribute
						Error 17: Undefined attribute type
						Error 18: Inappropriate matching
						Error 19: Constraint violation
						Error 20: Type or value exists
						Error 21: Invalid syntax
						Error 32: No such object
						Error 33: Alias problem
						Error 34: Invalid DN syntax
						Error 35: Entry is a leaf
						Error 36: Alias dereferencing problem
						Error 47: Proxy Authorization Failure
						Error 48: Inappropriate authentication
						Error 49: Invalid credentials
						Error 50: Insufficient access
						Error 51: Server is busy
						Error 52: Server is unavailable
						Error 53: Server is unwilling to perform
						Error 54: Loop detected
						Error 64: Naming violation
						Error 65: Object class violation
						Error 66: Operation not allowed on non-leaf
						Error 67: Operation not allowed on RDN
						Error 68: Already exists
						Error 69: Cannot modify object class
						Error 70: Results too large
						Error 71: Operation affects multiple DSAs
						Error 80: Internal (implementation specific) error
					 */
				}
			}
			else
			{
				$userMessage = "LOGIN FAILED\nAuthentication service error [30].\n";
			}
		}
		else
		{
			$userMessage = "LOGIN FAILED\nAuthentication service error [31].\n";
		}
		
	return $validCredentials;
}

function escLDAPChars($unsafeString)
{
	$reservedChars = array(
		chr(0x0A), // <LF> Line feed           0x0A
		chr(0x0D), // <CR> Carriage return     0x0D
		chr(0x22), // "    Double quote        0x22
		chr(0x23), // #    Number sign         0x23
		chr(0x2B), // +    Plus sign           0x2B
		chr(0x2C), // ,    Comma               0x2C
		chr(0x2F), // /    Forward slash       0x2F
		chr(0x3B), // ;    Semicolon           0x3B
		chr(0x3C), // <    Left angle bracket  0x3C
		chr(0x3D), // =    Equals sign         0x3D
		chr(0x3E), // >    Right angle bracket 0x3E
		chr(0x5C), // \    Backward slash      0x5C
		chr(0x2A)  // *    Asterisk            0x2A	
	);
	
	$unsafeChars = str_split($unsafeString);
	
	$safeString = '';
	foreach( $unsafeChars as $char )
	{
		if( in_array($char, $reservedChars) )
		{
			$safeString .= '\\';
		}
		$safeString .= $char;
	}
	
	return $safeString;
}