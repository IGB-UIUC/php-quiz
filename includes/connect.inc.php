<?php
/**
 * Create new PDO object all us to interact with the mysql database
 */
try{
    $sqlDataBase = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASSWORD);
}catch(PDOException $e)
{
    echo $e->getMessage();
}

?>