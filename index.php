<?php

require 'config.php';
require 'Slim/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//DB
$db = mysql_connect("localhost", "root", $database_password)
    or die("Keine Verbindung möglich: " . mysql_error());
mysql_select_db("timemgr") or die("MYSQL CONNECTION ERROR");

$app->get(
    '/',
    function () {
        echo "Hallo Welt!";
        
        $query = "SELECT * FROM projects";
        $result = mysql_query($query) or die("MYSQL ERROR: " . mysql_error());
        
        while ($line = mysql_fetch_array($result)) {
            echo $line['id'];
        }
    }
);

// POST route
/*$app->post(
    '/post',
    function () {
        echo 'This is a POST route';
    }
);

// PUT route
$app->put(
    '/put',
    function () {
        echo 'This is a PUT route';
    }
);

// PATCH route
$app->patch('/patch', function () {
    echo 'This is a PATCH route';
});

// DELETE route
$app->delete(
    '/delete',
    function () {
        echo 'This is a DELETE route';
    }
);*/

$app->run();

//mysql_free_result($result);
//mysql_close($db);