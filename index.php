<?php
ob_start();
session_start();
error_reporting(-1);
//Load initial configuration
include ("includes/config.php");

//Load php class auto loader
include ("includes/auto_load_class.php");

//Load PDO database object
include ("includes/connect.inc.php");


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
if(isset($_POST['logon']))
{
    $isAuthenticated = $authenticate->Login($_POST['username'],$_POST['password']);
}
else
{
    $isAuthenticated = $authenticate->VerifySession();
}

//Load page header
include ("includes/header.html");

if($isAuthenticated)
{
    if(isset($_GET['logout']))
    {
        $authenticate->Logout();
    }
    else
    {
        echo "<a href=\"index.php?logout=true\">Logout</a><br><br>";
    }
}
//Check page permissions and compare it to authenticated user permissions
if(
    ($PAGES[$pageSelected]['perm']=='all')
    || ($PAGES[$pageSelected]['perm']=='auth' && $isAuthenticated)
    || ($PAGES[$pageSelected]['perm']=='admin' && ($authenticate->getAuthenticatedUser()->getUserRole()== User::ROLE_ADMIN))
)
{
    //Load page selected if permission verified
    include($PAGES[$pageSelected]['path']);
}
else{
    //Ask user to login if permissions are not verified
    include($PAGES["login"]['path']);
}

//Load page footer
include ("includes/footer.html");

?>
