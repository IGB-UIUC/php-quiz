<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 5/14/14
 * Time: 11:59 AM
 */

class Authenticate {
    private $db;
    private $ldapAuth;
    private $authenticatedUser;
    private $logonError;
    private $verified;

    public function __construct(PDO $db, LdapAuth $ldapAuth)
    {
        $this->db = $db;
        $this->ldapAuth = $ldapAuth;
        $this->verified = false;
        $this->authenticatedUser = new User($this->db);
        $this->logonError="";
    }

    public function __destruct()
    {

    }

    /**Login into ldap
     * set authentication key
     * set session variables
     * @param $userName
     * @param $password
     * @return bool
     */
    public function Login($userName, $password)
    {
        $this->logonError = "";

        if ($this->ldapAuth->Authenticate ( $userName, $password, "" )) {
            $userId = $this->authenticatedUser->Exists($userName);
            if ($userId)
            {
                $this->authenticatedUser->LoadUser($userId);
                $this->authenticatedUser->UpdateAuthKey();
                $this->SetSession($this->authenticatedUser->getAuthKey(), $this->authenticatedUser->GetUserId() );
                $this->verified = true;
                return true;
            } else {
                $this->authenticatedUser = new User($this->db);
                $this->authenticatedUser->CreateUser($userName);
                $this->verified = true;
                $this->authenticatedUser->UpdateAuthKey();
                $this->SetSession($this->authenticatedUser->getAuthKey(), $this->authenticatedUser->GetUserId() );
                return true;
            }
        } else {
            $this->logonError = $this->logonError . "Incorrect user name or password.";
        }

        $this->verified=false;
        return false;
    }

    /**
     * Logout by unsetting session variables
     */
    public function Logout()
    {
        $this->UnsetSession();
        $this->verified = false;
    }

    /**Verify session information matches authen key
     * if not then log user out
     * if expried then log user out.
     * @return bool
     */
    public function VerifySession()
    {
        if(isset($_SESSION['training_user_id']))
        {
            if(time() - $_SESSION['training_created'] < 1800)
            {
                $this->authenticatedUser = new User ( $this->db);
                $this->authenticatedUser->LoadUser($_SESSION['training_user_id']);

                if($this->authenticatedUser->getAuthKey() == $_SESSION['training_key'])
                {
                    $this->authenticatedUser->UpdateAuthKey();
                    $this->SetSession($this->authenticatedUser->getAuthKey(), $this->authenticatedUser->GetUserId());
                }
                $this->verified = true;
                return true;
            }
        }
        $this->UnsetSession();
        $this->verified=false;
        return false;
    }

    /**Set session variables
     * @param $secureKey
     * @param $userId
     */
    public function SetSession($secureKey,$userId)
    {
        $_SESSION ['training_user_id'] = $userId;
        $_SESSION ['training_key'] = $secureKey;
        $_SESSION ['training_created'] = time();
    }

    /**
     * Unset session variables
     */
    public function UnsetSession()
    {
        unset ($_SESSION);
	session_destroy();
    }

    /**
     * Returns an encrypted & utf8-encoded
     */
    private function encrypt($pure_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
        return $encrypted_string;
    }

    /**
     * Returns decrypted original string
     */
    private function decrypt($encrypted_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }

    /**
     * @return mixed
     */
    public function getAuthenticatedUser()
    {
        return $this->authenticatedUser;
    }

    /**
     * @return mixed
     */
    public function getLogonError()
    {
        return $this->logonError;
    }

    /**
     * @return boolean
     */
    public function isVerified()
    {
        return $this->verified;
    }
}
