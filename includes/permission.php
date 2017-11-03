<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 7/28/14
 * Time: 1:18 PM
 */
$user = new User($sqlDataBase);

if(isset($_POST['submit_admin']))
{
    $user->LoadUser($_POST['admin_user']);
    $user->SetRole(User::ROLE_ADMIN);
}

if(isset($_POST['submit_moderator']))
{
    $user->LoadUser($_POST['admin_user']);
    $user->SetRole(User::ROLE_MODERATOR);
}

if(isset($_GET['action']))
{
    if(isset($_GET['user_id']))
    {
        $user->LoadUser($_GET['user_id']);
        $user->SetRole(User::ROLE_USER);
    }
}
?>
<div class="panel panel-primary">
    <div class="panel-heading"><h3>User Permissions</h3></div>
    <div class="panel-body">
    <a href="index.php?p=admin"><< Back</a><br><br>
<form action="index.php?p=permission" method="post">

    Set user:
    <select name="admin_user">
        <?php
        $usersList = $user->ListUsers(User::ROLE_USER);
        foreach($usersList as $id=>$userInfo)
        {
            echo "<option value=\"".$userInfo['user_id']."\">".$userInfo['user_name']."</option>";
        }
        ?>
    </select>
    <input type="submit" value=" Set Admin " name="submit_admin" class="btn btn-primary">
    <input type="submit" value=" Set Moderator " name="submit_moderator" class="btn btn-primary">
</form>
        <br>
<table class="table">
    <th>User Name</th>
    <th>Role</th>
    <th>Option</th>
    <?php
    $adminList = $user->ListUsers(User::ROLE_ADMIN);
    $modList = $user->ListUsers(User::ROLE_MODERATOR);

    foreach($adminList as $id=>$adminInfo)
    {
        echo "<tr><td>".$adminInfo['user_name']."</td><td>ADMIN</td><td><a href=\"index.php?p=permission&action=demote&user_id=".$adminInfo['user_id']."\">Demote</a></td></tr>";
    }

    foreach($modList as $id=>$modInfo)
    {
        echo "<tr><td>".$modInfo['user_name']."</td><td>MODERATOR</td><td><a href=\"index.php?p=permission&action=demote&user_id=".$modInfo['user_id']."\">Demote</a></td></tr>";
    }

    ?>
</table>
</div></div>
