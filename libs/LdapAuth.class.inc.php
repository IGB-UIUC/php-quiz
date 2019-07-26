<?php


class LdapAuth
{
	var $host="";
	var $peopleDN="";
	var $groupDN="";
	var $baseDN="";
	var $ssl="";
	var $port="";
	var $group="";
	var $error="No Error Detected";
	var $ldap_resource = false;	
	var $groupMemberAttribute = "memberuid";

	public function __construct($host,$peopleDN,$groupDN,$baseDN,$ssl,$port)
	{
		$this->host=$host;
		$this->peopleDN=$peopleDN;
		$this->groupDN=$groupDN;
		$this->baseDN=$baseDN;
		$this->ssl=$ssl;
		$this->port=$port;
		$this->connect();
	}
	
	public function __destruct()
	{
	
	}

	public function get_resource() {
		return $this->ldap_resource;
	}
	public function get_connection() {
		return is_resource($this->ldap_resource);
	}

	private function connect() {
		                $ldap_uri;
                if ($this->ssl) {
                        $ldap_uri = "ldaps://" . $this->host . ":" . $this->port;
                }
                elseif (!$this->ssl) {
                        $ldap_uri = "ldap://" . $this->host . ":" . $this->port;
                }
		$this->ldap_resource = ldap_connect($ldap_uri);
		if ($this->get_connection()) {
			return true;
		}
		return false;



	}
    /**Authenticate with user information with LDAP
     * @param $username
     * @param $password
     * @param $group
     * @return bool|int
     */
    public function Authenticate($username,$password,$group) {

                 
        	$bindDN = "uid=" . $username . "," . $this->peopleDN;
       
       	 	$success = @ldap_bind($this->get_resource(), $bindDN, $password);
		
        	if ($success == 1 && $group!="") {
                	$search = ldap_search($this->get_resource(),$this->groupDN,"(cn=" . $group . ")");
                	$data = ldap_get_entries($this->get_resouce(),$search);
              
                	foreach($data[0][$groupMemberAttribute] as $groupMember) {
                       
                        	if ($username == $groupMember) {
                                	$success = 1;
                                	return $success;
                        	}
                        	else {
                                	$success = 0;
                        	}
                	}
               
        	}
		if($success == 0)
		{
			$error=ldap_error($this->get_resource());
		}	
        	return $success;
	}



        public function search($filter,$ou = "",$attributes = "") {
		$result = false;
		if ($ou == "") {
			$ou = $this->get_base_dn();
		}
		if (($this->get_connection()) && ($attributes != "")) {
	                $ldap_result = ldap_search($this->get_resource(),$ou,$filter,$attributes);
	                $result = ldap_get_entries($this->get_resource(),$ldap_result);
		}
		elseif (($this->get_connection()) && ($attributes == "")) {
			$ldap_result = ldap_search($this->get_resource(),$ou,$filter);
                        $result = ldap_get_entries($this->get_resource(),$ldap_result);

		}
		return $result;

        }

	public function get_base_dn() {
		return $this->baseDN;
	}
}


?>
