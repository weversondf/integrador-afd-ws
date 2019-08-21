<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<?php

ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 0);
/*** error reporting on ***/
error_reporting(E_ALL);

/*** define the site path ***/
$site_path = realpath(dirname(__FILE__));
define('__SITE_PATH', $site_path);

/*** include the init.php file ***/
include 'includes/init.php';

/*** load the router ***/
$registry->router = new router($registry);

/*** set the controller path ***/
$registry->router->setPath(__SITE_PATH . '/controller');

/*** load up the template ***/
$registry->template = new template($registry);
//die('<pre>'.print_r($registry->router,1));
/*** load the controller ***/
$registry->router->loader();


?>

</body>
</html>
