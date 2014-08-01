<?php

function class_autoload($class_name) {
    if(file_exists('classes/' . $class_name . '.php'))
    {
        include 'classes/' . $class_name . '.php';
    }


}
spl_autoload_register('class_autoload');
?>
