

<br />
<?php
/**
 * includes/login.php
 * Show a login prompt
 */
//Build GET url so when we submit the login form we will go to the original destination
$getUrl = "";
foreach($_GET as $name => $value)
{
    if($name!='logout')
    {
    	$getUrl .= $name."=".$value."&";
    }
}

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3>IGB Online Safety Exam Login</h3>
    </div>
    <div class="panel-body">
			<form method="post" action="index.php?<?php echo $getUrl; ?>" name="login">
                <div class="form-group">
			<label>Netid:</label>
			<div class='input-group'>
				<input class="form-control" type="text" name="username" value="<?php if (isset($_POST['username'])) { echo $_POST['username']; } ?>">
				<div class='input-group-addon'>
					<span class='glyphicon glyphicon-user' aria-hidden='true'></span>
				</div>
			</div>
                 </div>
                <div class="form-group">
			<label>Password:</label>
			<div class='input-group'>
				<input class="form-control" type="password" name="password">
				<div class='input-group-addon'>
					<span class='glyphicon glyphicon-lock' aria-hidden='true'></span>
				</div>
			</div>
                </div>
				<input type="submit" value="Login" name="logon" alt="submit" class="btn btn-lg btn-primary" >
			</form>
        </div>
    </div>

<?php 
if($authenticate->getLogonError())
{
    echo "<div class=\"alert alert-danger\" role=\"alert\">";
    echo "<b>".$authenticate->getLogonError()."</b> <br>Please try again please.";
    echo "</div>";
}
?>
