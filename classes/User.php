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
    private $sqlDataBase;
    private $authKey;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
    }

    public function __destruct()
    {

    }


    /**Load user form database into this object
     * @param $userId
     */
    public function LoadUser($userId)
    {
        $queryUser = "SELECT * FROM users WHERE user_id=:user_id";
        $user = $this->sqlDataBase->prepare($queryUser);
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
        $queryInsertUser = "INSERT INTO users (user_name,user_role)VALUES(:user_name,:user_role)";
        $insertUser = $this->sqlDataBase->prepare($queryInsertUser);
        $insertUser->execute(array(':user_name'=>$userName,':user_role'=>User::ROLE_USER));
        $userId = $this->sqlDataBase->lastInsertId();
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
        $queryUsersList = "SELECT * FROM users WHERE user_role=:user_role ORDER BY user_name";
        $usersList = $this->sqlDataBase->prepare($queryUsersList);
        $usersList->execute(array(':user_role'=>$userRole));
        return $usersList->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Update authentication key for this user
     * used to make sure the session doesn't get hijacked.
     */
    public function UpdateAuthKey()
    {
        $queryUpdateSecureKey = "UPDATE users SET auth_key=MD5(RAND()) WHERE user_id = :user_id";
        $updateSecureKey = $this->sqlDataBase->prepare($queryUpdateSecureKey);
        $updateSecureKey->execute(array(":user_id"=>$this->userId));

        $queryGetSecureKey = "SELECT auth_key FROM users WHERE user_id = :user_id";
        $secureKey = $this->sqlDataBase->prepare($queryGetSecureKey);
        $secureKey->execute(array(":user_id"=>$this->userId));
        $secureKeyArr = $secureKey->fetch(PDO::FETCH_ASSOC);
        $this->authKey = $secureKeyArr['auth_key'];
    }

    /**Checks if a user name already exists in the database
     * if it does then return the corresponding user id
     * @param $userName
     * @return bool
     */
    public function Exists($userName)
    {
        $queryUserExists = "SELECT * FROM users WHERE user_name=:user_name";
        $userExists = $this->sqlDataBase->prepare($queryUserExists);
        $userExists->execute(array(':user_name'=>$userName));
        $userExistsArr = $userExists->fetch(PDO::FETCH_ASSOC);

        if(count($userExistsArr))
        {
            return $userExistsArr['user_id'];
        }

        return false;
    }

    /**Set the role of this user
     * User::ADMIN_ROLE or User::USER_ROLE
     * @param $roleId
     */
    public function SetRole($roleId)
    {
        $queryUpdateRole = "UPDATE users SET user_role=:user_role WHERE user_id=:user_id";
        $updateRole = $this->sqlDataBase->prepare($queryUpdateRole);
        $updateRole->execute(array(':user_role'=>$roleId,':user_id'=>$this->userId));
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
