<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

<script type='text/javascript' src='vendor/components/jquery/jquery.min.js'></script>
<script type='text/javascript' src='vendor/twbs/bootstrap/dist/js/bootstrap.min.js'></script>


<link rel="stylesheet" type="text/css" href='vendor/twbs/bootstrap/dist/css/bootstrap.min.css'>

<title><?php echo TITLE; ?></title>
</head>

<body>
<nav class="navbar navbar-default">
	<div class='container-fluid'>
	        <a class='navbar-brand' href='#'><?php echo TITLE; ?></a>
<?php
if($isAuthenticated)
{
	echo "<span class='pull-right'>Version: " . __VERSION__ . "&nbsp";
	echo "<a class='btn btn-primary btn-sm' role='button' href='index.php?logout=true'>Logout</a>";
	if($authenticate->getAuthenticatedUser()->getUserRole()== User::ROLE_ADMIN || $authenticate->getAuthenticatedUser()->getUserRole()== User::ROLE_MODERATOR)
	{
                echo "&nbsp; <a class='btn btn-danger btn-sm' role='button' href=\"index.php?p=admin\">Admin</a>";
        }
        echo "</span>";
}
?>
	</div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
