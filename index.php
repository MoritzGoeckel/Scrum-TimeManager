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
        echo "The docu... sometime... maybe.";
    }
);

//Projects
$app->get('/projects/:uid', function ($uid) {
    $query = "SELECT projects.name as projectName, projects.id as projectId,
    user.name as autorName, user.id as autorId
    FROM user_in_project
    LEFT JOIN projects ON projects.id = user_in_project.project
    LEFT JOIN user ON user.id = projects.author
    WHERE user_in_project.user = " . $uid;
    
    $result = mysql_query($query) or die("MYSQL ERROR: " . mysql_error());
    
    $output = array();
    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) { 
        array_push($output, $line);                                           
    }
    
    echo json_encode($output);
});

//Tasks
$app->get('/project/:pid/tasks', function ($pid) {
    
});

$app->get('/sprint/:sid/tasks/', function ($sid) {
    
});

$app->get('/user/:uid/tasks/', function ($uid) {
    
});

//User
$app->get('/user/:uid', function ($uid) {
    
});

//Sprint
$app->get('/sprint/:sid', function ($sid) {
    
});

// POST route
/*$app->post(
    '/post',
    function () {
        echo 'This is a POST route';
    }
);*/

$app->run();

//mysql_free_result($result);
//mysql_close($db);