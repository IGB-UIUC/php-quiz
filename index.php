<?php
ob_start();
session_start();
//error_reporting(-1);
//Load initial configuration
require_once ("includes/main.inc.php");


//Set the default page to load
$pageSelected = DEFAULT_PAGE;

//If page was selected
if(isset($_GET['p']))
{
    $pageSelected=$_GET['p'];
}

//Setup ldap authentication classes
$ldapAuth = new LdapAuth(LDAP_HOST,LDAP_PEOPLE_DN,LDAP_GROUP_DN,LDAP_SSL,LDAP_PORT);
$authenticate = new Authenticate($sqlDataBase,$ldapAuth);

//Authenticate User
$isauthenticated = false;
if(isset($_POST['logon']))
{
    $isAuthenticated = $authenticate->Login($_POST['username'],$_POST['password']);
}
else
{
    $isAuthenticated = $authenticate->VerifySession();
}


if($isAuthenticated)
{
    if(isset($_GET['logout']))
    {
        $authenticate->Logout();
	$isAuthenticated = false;
    }
}

//Load page header
require_once ("includes/header.inc.php");

//Check page permissions and compare it to authenticated user permissions
if(
    ($PAGES[$pageSelected]['perm']=='all')
    || ($PAGES[$pageSelected]['perm']=='auth' && $isAuthenticated)
    || ($PAGES[$pageSelected]['perm']=='admin' && ($authenticate->getAuthenticatedUser()->getUserRole()== User::ROLE_ADMIN && $isAuthenticated))
    || ($PAGES[$pageSelected]['perm']=='mod' && ($authenticate->getAuthenticatedUser()->getUserRole()== User::ROLE_MODERATOR && $isAuthenticated || ($authenticate->getAuthenticatedUser()->getUserRole()== User::ROLE_ADMIN && $isAuthenticated)))
)
{
    //Load page selected if permission verified
    require_once($PAGES[$pageSelected]['path']);
}
else{
    //Ask user to login if permissions are not verified
    require_once($PAGES["login"]['path']);
}

//Load page footer
require_once ("includes/footer.inc.php");

?>
