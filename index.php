<?php

require 'config.php';
require 'Slim/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//DB
$db = mysql_connect("localhost", "root", $database_password)
    or die("Keine Verbindung möglich: " . mysql_error());
mysql_select_db("timemgr") or die("MYSQL CONNECTION ERROR");

function runAndOutputSql($query){
    $result = mysql_query($query) or die("MYSQL ERROR: " . mysql_error());
    $output = array();
    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) { 
        array_push($output, $line);                                           
    }
    
    echo json_encode($output);
}

function execQuery(){
    mysql_query($query) or die("MYSQL ERROR: " . mysql_error());
    echo "Done";
}

//AUTH
$vars = $app->request->post();
if(!isset($vars['uid']) or !isset($vars['secret']))
    die("auth parameters missing");

$result = mysql_query("SELECT * FROM user WHERE id = " . $vars['uid'] . " AND secret = " . $vars['secret']);

if(mysql_num_rows($result) != 1)
    die("auth failed");

//The USER
$user = mysql_fetch_array($result, MYSQL_ASSOC)

// ##### THE GET API #####

$app->get(
    '/',
    function () {
        echo "The docu... sometime... maybe.";
    }
);

//User
$app->get('/user/:uid', function ($uid) {
    $query = "SELECT name
    FROM user
    WHERE id = " . $uid;
    
    runAndOutputSql($query);
});

$app->get('/user/:uid/projects/', function ($uid) {
    $query = "SELECT projects.name as projectName, projects.id as projectId,
    user.name as autorName, user.id as autorId
    FROM user_in_project
    LEFT JOIN projects ON projects.id = user_in_project.project
    LEFT JOIN user ON user.id = projects.author
    WHERE user_in_project.user = " . $uid;
    
    runAndOutputSql($query);
});

$app->get('/user/:uid/tasks/', function ($uid) {
    $query = "SELECT tasks.id as taskId, tasks.name as taskName,
    author.name as autorName, author.id as autorId
    FROM tasks
    LEFT JOIN user as author ON author.id = tasks.author
    WHERE tasks.assignee = " . $uid;
    
    runAndOutputSql($query);
});

//Project
$app->get('/project/:pid/tasks', function ($pid) {
    $query = "SELECT tasks.id as taskId, tasks.name as taskName,
    author.name as autorName, author.id as autorId,
    assignee.name as assigneeName, assignee.id as assigneeId
    FROM tasks
    LEFT JOIN user as author ON author.id = tasks.author
    LEFT JOIN user as assignee ON assignee.id = tasks.assignee
    WHERE tasks.project = " . $pid;
    
    runAndOutputSql($query);
});

$app->get('/project/:pid/sprints', function ($pid) {
    $query = "SELECT *
    FROM sprints
    WHERE project = " . $pid;
    
    runAndOutputSql($query);
});

//Sprint
$app->get('/sprint/:sid', function ($sid) {
    $query = "SELECT *
    FROM sprints
    WHERE id = " . $sid;
    
    runAndOutputSql($query);
});

$app->get('/sprint/:sid/tasks/', function ($sid) {
    $query = "SELECT tasks.id as taskId, tasks.name as taskName,
    author.name as autorName, author.id as autorId,
    assignee.name as assigneeName, assignee.id as assigneeId
    FROM tasks_in_sprint
    LEFT JOIN tasks ON tasks_in_sprint.task = tasks.id
    LEFT JOIN user as author ON author.id = tasks.author
    LEFT JOIN user as assignee ON assignee.id = tasks.assignee
    WHERE tasks_in_sprint.sprint = " . $sid;
    
    runAndOutputSql($query);
});

// ##### THE INSERT API #####

$app->post(
    '/add/user',
    function () {
        if(isset($vars['name']) && isset($vars['mail']))
            execQuery("INSERT INTO user (name, mail) VALUES ('" . $vars['name'] . "', '" . $vars['mail'] . "')");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/project',
    function () {
        if(isset($vars['name']))
            execQuery("INSERT INTO projects (name, author) VALUES ('" . $vars['name'] . "', " . $user['id'] . ")");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/sprint',
    function () {
        if(isset($vars['name']) && isset($vars['project']) && isset($vars['start'])) && isset($vars['end'])))
            execQuery("INSERT INTO sprints (name, project, start, end) VALUES ('" . $vars['name'] . "', " . $vars['project'] . ", '" .$vars['start']. "', '".$vars['end']."')");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/task',
    function () {
        if(isset($vars['name']) && isset($vars['description']) && isset($vars['effort']) && isset($vars['assignee']) && isset($vars['priority']) && isset($vars['project']))
            execQuery("INSERT INTO tasks (name, description, author, effort, assignee, priority, project) VALUES 
                                         ('".$vars['name']."', '".$vars['description']."', ".$user['id'].", ".$vars['effort'].", ".$vars['assignee'].", ".$vars['priority'].", ".$vars['project'].")");
        else 
            echo ("parameters missing");
    }
);

// ##### THE UPDATE API #####

$app->run();

//mysql_free_result($result);
//mysql_close($db);