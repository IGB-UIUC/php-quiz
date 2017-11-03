

<br />
<?php
/**
 * includes/login.php
 * Show a login prompt
 */
if($authenticate->getLogonError())
{
    echo "<div class=\"alert alert-danger\" role=\"alert\">";
    echo "<b>".$authenticate->getLogonError()."</b> <br>Please try again please.";
    echo "</div>";
}
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
					<input class="form-control" type="text" name="username">
                 </div>
                <div class="form-group">
					<label>Password:</label>
					<input class="form-control" type="password" name="password">
                </div>
				<input type="submit" value="Login" name="logon" alt="submit" class="btn btn-lg btn-primary" >
			</form>
        </div>
    </div>

