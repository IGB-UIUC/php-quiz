<?php

require_once "conf/app.inc.php";
require_once "conf/config.inc.php";
require_once 'vendor/autoload.php';

function class_autoload($class_name) {
    if(file_exists('libs/' . $class_name . '.class.inc.php'))
    {
        include 'libs/' . $class_name . '.class.inc.php';
    }


}
spl_autoload_register('class_autoload');


try{
    $sqlDataBase = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASSWORD);
}catch(PDOException $e)
{
    echo $e->getMessage();
}





?>
