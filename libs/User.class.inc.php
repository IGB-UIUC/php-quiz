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




    private $userId;
    private $db;
    private $authKey;

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
    }

    /**Create a new user in database and load it into this object
     * @param $userName
     */
    public function CreateUser($userName)
    {
        $sql = "INSERT INTO users (user_name,user_role)VALUES(:user_name,:user_role)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':user_name'=>$userName,':user_role'=>User::ROLE_USER));
        $userId = $this->db->lastInsertId();
        if($userId)
        {
            $this->userId = $userId;
            $this->userName = $userName;
            $this->UpdateAuthKey();
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
        $sql = "UPDATE users SET auth_key=MD5(RAND()) WHERE user_id = :user_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(":user_id"=>$this->userId));

        $sql = "SELECT auth_key FROM users WHERE user_id = :user_id";
        $query = $this->db->prepare($sql);
        $query->execute(array(":user_id"=>$this->userId));
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $this->authKey = $result['auth_key'];
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
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if(count($result)) {
            return $result['user_id'];
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

} 
