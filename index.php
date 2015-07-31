<?php

/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);*/

require 'config.php';
require 'Slim/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

//DB
$db = mysql_connect("localhost", "root", $database_password)
    or die("Connection to db failed: " . mysql_error());
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
function auth(){
    $vars = $app->request->post();
    if(!isset($vars['uid']) or !isset($vars['secret']))
        die("auth parameters missing");
    
    $result = mysql_query("SELECT * FROM user WHERE id = " . $vars['uid'] . " AND secret = " . $vars['secret']);
    
    if(mysql_num_rows($result) != 1)
        die("auth failed");   
        
    //The USER
    return mysql_fetch_array($result, MYSQL_ASSOC);
}

// ##### THE GET API #####

$app->get(
    '/',
    function () {
        echo "The docu... sometime... maybe.";
    }
);

//User
$app->get('/user/:uid', function ($uid) {
    auth();
    
    $query = "SELECT name
    FROM user
    WHERE id = " . $uid;
    
    runAndOutputSql($query);
});

$app->get('/user/:uid/projects/', function ($uid) {
    auth();
    
    $query = "SELECT projects.name as projectName, projects.id as projectId,
    user.name as autorName, user.id as autorId
    FROM user_in_project
    LEFT JOIN projects ON projects.id = user_in_project.project
    LEFT JOIN user ON user.id = projects.author
    WHERE user_in_project.user = " . $uid;
    
    runAndOutputSql($query);
});

$app->get('/user/:uid/tasks/', function ($uid) {
    auth();
    
    $query = "SELECT tasks.id as taskId, tasks.name as taskName,
    author.name as autorName, author.id as autorId
    FROM tasks
    LEFT JOIN user as author ON author.id = tasks.author
    WHERE tasks.assignee = " . $uid;
    
    runAndOutputSql($query);
});

//Project
$app->get('/project/:pid/tasks', function ($pid) {
    auth();
    
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
    auth();
    
    $query = "SELECT *
    FROM sprints
    WHERE project = " . $pid;
    
    runAndOutputSql($query);
});

//Sprint
$app->get('/sprint/:sid', function ($sid) {
    auth();
    
    $query = "SELECT *
    FROM sprints
    WHERE id = " . $sid;
    
    runAndOutputSql($query);
});

$app->get('/sprint/:sid/tasks/', function ($sid) {
    auth();
    
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
        auth();
        
        if(isset($vars['name']) && isset($vars['mail']))
            execQuery("INSERT INTO user (name, mail) VALUES ('" . $vars['name'] . "', '" . $vars['mail'] . "')");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/project',
    function () {
        $user = auth();
        
        if(isset($vars['name']))
            execQuery("INSERT INTO projects (name, author) VALUES ('" . $vars['name'] . "', " . $user['id'] . ")");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/sprint',
    function () {
        if(isset($vars['name']) && isset($vars['project']) && isset($vars['start']) && isset($vars['end']))
            execQuery("INSERT INTO sprints (name, project, start, end) VALUES ('" . $vars['name'] . "', " . $vars['project'] . ", '" .$vars['start']. "', '".$vars['end']."')");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/task',
    function () {
        $user = auth();
        
        if(isset($vars['name']) && isset($vars['description']) && isset($vars['effort']) && isset($vars['assignee']) && isset($vars['priority']) && isset($vars['project']))
            execQuery("INSERT INTO tasks (name, description, author, effort, assignee, priority, project) VALUES 
                                         ('".$vars['name']."', '".$vars['description']."', ".$user['id'].", ".$vars['effort'].", ".$vars['assignee'].", ".$vars['priority'].", ".$vars['project'].")");
        else 
            echo ("parameters missing");
    }
);

// ##### THE UPDATE API #####

$app->get(
    '/update/user/:uid',
    function ($uid) {
        auth();
        
        $update = "";
        if(isset($vars['name']))
            $update .= "name = '" . $vars['name'] . "' ";
            
        if(isset($vars['mail']))
            $update .= "mail = '" . $vars['mail'] . "' ";
        
        if($update != "")
            execQuery("UPDATE user SET ".$update." WHERE id = " . $uid);
        else
            echo ("parameters missing");
    }
);

$app->get(
    '/update/project/:pid',
    function ($pid) {
        $user = auth();
        
        $update = "";
        if(isset($vars['name']))
            $update .= "name = '" . $vars['name'] . "' ";
            
        if(isset($vars['author']))
            $update .= "author = '" . $vars['author'] . "' ";
        
        if($update != "")
            execQuery("UPDATE projects SET ".$update." WHERE id = " . $pid . " AND author = " . $user['id']);
        else
            echo ("parameters missing");
    }
);

$app->get(
    '/update/task/:tid',
    function ($tid) {
        $user = auth();
        
        $update = "";
        if(isset($vars['name']))
            $update .= "name = '" . $vars['name'] . "' ";
            
        if(isset($vars['description']))
            $update .= "description = '" . $vars['description'] . "' ";
            
        if(isset($vars['effort']))
            $update .= "effort = " . $vars['effort'] . " ";
            
        if(isset($vars['assignee']))
            $update .= "assignee = " . $vars['assignee'] . " ";
            
        if(isset($vars['priority']))
            $update .= "priority = " . $vars['priority'] . " ";
            
        if(isset($vars['project']))
            $update .= "project = " . $vars['project'] . " ";
            
        if(isset($vars['used']))
            $update .= "used = " . $vars['used'] . " ";
        
        if($update != "")
            execQuery("UPDATE tasks SET ".$update." WHERE id = " . $tid . " AND author = " . $user['id']);
        else
            echo ("parameters missing");
    }
);

$app->run();

//mysql_free_result($result);
//mysql_close($db);