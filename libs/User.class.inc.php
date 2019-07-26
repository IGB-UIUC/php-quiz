<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 6/20/14
 * Time: 9:57 AM
 */


class User {

    const ROLE_USER=0;
    const ROLE_ADMIN=1;
    const ROLE_MODERATOR=2;
    private $userName;
    private $userRole;
    private $firstName;
    private $lastName;
    private $userId;
    private $db;
    private $authKey;
    private $email;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function __destruct()
    {

    }


    /**Load user form database into this object
     * @param $userId
     */
    public function LoadUser($userId)
    {
        $sql = "SELECT * FROM users WHERE user_id=:user_id LIMIT 1";
        $user = $this->db->prepare($sql);
        $user->execute(array(':user_id'=>$userId));
        $userInfo = $user->fetch(PDO::FETCH_ASSOC);
        $this->userId = $userId;
        $this->userName = $userInfo['user_name'];
        $this->userRole = $userInfo['user_role'];
        $this->authKey = $userInfo['auth_key'];

	$this->firstName = $userInfo['first_name'];
	$this->lastName = $userInfo['last_name'];
	$this->email = $userInfo['email'];
    }

    /**Create a new user in database and load it into this object
     * @param $userName
     */
    public function CreateUser($userName,&$ldapAuth)
    {
	$key = $this->generate_key();
	$filter = "(uid=" . $userName . ")";
	$ldap_attributes = $this->getLdapAttributes($ldapAuth,$userName);
        $sql = "INSERT INTO users (user_name,auth_key,user_role,first_name,last_name,email) ";
	$sql .= "VALUES(:user_name,:auth_key,:user_role,:first_name,:last_name,:email)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_name'=>$userName,
		':auth_key'=>$key,
		':user_role'=>User::ROLE_USER,
		':first_name'=>$ldap_attributes['firstName'],
		':last_name'=>$ldap_attributes['lastName'],
		':email'=>$ldap_attributes['email']
		));
        $userId = $this->db->lastInsertId();
        if($userId)
        {
		$this->LoadUser($userId);
        }

    }

    /**List users by role either admins or users
     * User::ADMIN_ROLE or User::USER_ROLE
     * @param $userRole
     * @return array
     */
    public function ListUsers($userRole)
    {
        $sql = "SELECT * FROM users WHERE user_role=:user_role ORDER BY user_name";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_role'=>$userRole));
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Update authentication key for this user
     * used to make sure the session doesn't get hijacked.
     */
    public function UpdateAuthKey()
    {
     
	$key = $this->generate_key();
	$sql = "UPDATE users SET auth_key= :auth_key  WHERE user_id = :user_id LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute(array(":auth_key"=>$key,":user_id"=>$this->userId));
        $this->authKey = $key;
    }

    private function generate_key() {
	$key = uniqid (rand (),true);
	$hash = sha1($key);
	return $hash;
    }
    /**Checks if a user name already exists in the database
     * if it does then return the corresponding user id
     * @param $userName
     * @return bool
     */
    public function Exists($userName)
    {
        $sql = "SELECT * FROM users WHERE user_name=:user_name LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_name'=>$userName));
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if(count($result)) {
            return $result[0]['user_id'];
        }

        return false;
    }

    /**Set the role of this user
     * User::ADMIN_ROLE or User::USER_ROLE
     * @param $roleId
     */
    public function SetRole($roleId)
    {
        $sql = "UPDATE users SET user_role=:user_role WHERE user_id=:user_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_role'=>$roleId,':user_id'=>$this->userId));
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return mixed
     */
    public function getUserRole()
    {
        return $this->userRole;
    }

    /**
     * @return mixed
     */
    public function getAuthKey()
    {
        return $this->authKey;

    }

    public function getFirstName() {
	return $this->firstName;

    }

    public function getLastName() {
	return $this->lastName;
   }
	
	public function getEmail() {
		return $this->email;
	}
	public function updateInfo(&$ldapAuth) {
		$update = false;
		$ldap_attributes = $this->getLdapAttributes($ldapAuth,$this->getUserName());

		if (($this->getFirstName() == "") || ($this->getLastName() == "") || ($this->getEmail() == "")) {
			$update = true;
		}
		if (($this->getFirstName() != $ldap_attributes['firstName']) || 
				($this->getLastName() != $ldap_attributes['lastName']) || 
				($this->getEmail() != $ldap_attributes['email'])) {

			$update = true;
		}

		if ($update) {
			$sql = "UPDATE users SET first_name=:first_name,last_name=:last_name,email=:email ";
			$sql .= "WHERE user_id=:user_id LIMIT 1";

			$query = $this->db->prepare($sql);
		        $query->execute(array(':user_id'=>$this->getUserId(),
                		':first_name'=>$ldap_attributes['firstName'],
 				':last_name'=>$ldap_attributes['lastName'],
				':email'=>$ldap_attributes['email']
			));
		}
		return $update;	

	}
	private function getLdapAttributes(&$ldapAuth,$userName) {
		$filter = "(uid=" . $userName . ")";
        	$attributes = array('mail','givenname','sn');
	        $result = $ldapAuth->search($filter,"",$attributes);
		$firstName = "";
		$lastName = "";
		$email = "";
		if (count($result)) {
			if (isset($result[0]['givenname'][0])) {
	        		$firstName = $result[0]['givenname'][0];
			}
			if (isset($result[0]['sn'][0])) {
		        	$lastName = $result[0]['sn'][0];
			}
			if (isset($result[0]['mail'][0])) {
        			$email = $result[0]['mail'][0];
			}
			return array('firstName'=>$firstName,'lastName'=>$lastName,'email'=>$email);
		}
		return false;	

	}
} 
