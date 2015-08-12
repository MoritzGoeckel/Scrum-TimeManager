<?php

/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);*/

require 'config.php';
require 'Slim/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

function writeLog($msg){
    $file = fopen("api_log.txt","a");
    fwrite($file, $msg . "\n");
    fclose($file);
}

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

function execQuery($query){
    writeLog($query);
    mysql_query($query) or die(writeLog("MYSQL ERROR: " . mysql_error()));
    echo "Done";
}

//Escape Strings mysql_real_escape_string

//AUTH
function auth(){
    global $app;
    global $vars;
    $vars = json_decode($app->environment['slim.input'], true);
    
    if(!isset($vars['auth']) or !isset($vars['auth']['uid']) or !isset($vars['auth']['secret']))
        die("auth parameters missing");
    
    $result = mysql_query("SELECT * FROM user WHERE id = " . $vars['auth']['uid'] . " AND secret = " . $vars['auth']['secret']);
    
    if(mysql_num_rows($result) != 1)
        die("auth failed");   
        
    //The USER
    return mysql_fetch_array($result, MYSQL_ASSOC);
}

// ##### THE GET API #####

$app->post(
    '/',
    function () {
        echo "The docu... sometime... maybe.";
    }
);

//User
$app->post('/login/:mail/:pw', function ($mail, $pw) {
    $query = "SELECT name, secret, mail, id
    FROM user
    WHERE pw = '" . $pw . "' " .
    "AND (mail = '".$mail."' or name = '".$pw."')";
    
    runAndOutputSql($query);
});

$app->post('/user/:uid', function ($uid) {
    auth();
    
    $query = "SELECT name, id, mail
    FROM user
    WHERE id = " . $uid;
    
    runAndOutputSql($query);
});

$app->post('/user/:uid/projects/', function ($uid) use ($app){
    auth();
    
    $query = "SELECT projects.name as projectName, projects.id as projectId,
    user.name as autorName, user.id as autorId
    FROM user_in_project
    LEFT JOIN projects ON projects.id = user_in_project.project
    LEFT JOIN user ON user.id = projects.author
    WHERE user_in_project.user = " . $uid;
    
    runAndOutputSql($query);
});

$app->post('/user/:uid/tasks/', function ($uid) {
    auth();
    
    $query = "SELECT tasks.id as taskId, tasks.name as taskName,
    author.name as autorName, author.id as autorId
    FROM tasks
    LEFT JOIN user as author ON author.id = tasks.author
    WHERE tasks.assignee = " . $uid;
    
    runAndOutputSql($query);
});

//tasks
$app->post('/task/:uid', function ($uid) {
    auth();
    
    $query = "SELECT tasks.id as taskId, tasks.name as taskName,
    author.name as autorName, author.id as autorId
    FROM tasks
    LEFT JOIN user as author ON author.id = tasks.author 
    WHERE tasks.id = " . $uid;
    
    runAndOutputSql($query);
});

//Project
$app->post('/project/:pid', function ($pid) {
    auth();
    
    $query = "SELECT *
    FROM projects
    WHERE id = " . $pid;
    
    runAndOutputSql($query);
});

$app->post('/project/:pid/tasks', function ($pid) {
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

$app->post('/project/:pid/sprints', function ($pid) {
    auth();
    
    $query = "SELECT *
    FROM sprints
    WHERE project = " . $pid;
    
    runAndOutputSql($query);
});

//Sprint
$app->post('/sprint/:sid', function ($sid) {
    auth();
    
    $query = "SELECT *
    FROM sprints
    WHERE id = " . $sid;
    
    runAndOutputSql($query);
});

$app->post('/sprint/:sid/tasks/', function ($sid) {
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
        
        global $vars;
        $data = $vars['data'];
        
        if(isset($data['name']) && isset($data['mail']))
            execQuery("INSERT INTO user (name, mail) VALUES ('" . $data['name'] . "', '" . $data['mail'] . "')");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/project',
    function () {
        $user = auth();
        
        global $vars;
        $data = $vars['data'];
        
        if(isset($data['name']))
            execQuery("INSERT INTO projects (name, author) VALUES ('" . $data['name'] . "', " . $data['id'] . ")");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/sprint',
    function () {
        
        global $vars;
        $data = $vars['data'];
        
        if(isset($data['name']) && isset($data['project']) && isset($data['start']) && isset($data['end']))
            execQuery("INSERT INTO sprints (name, project, start, end) VALUES ('" . $data['name'] . "', " . $data['project'] . ", '" .$data['start']. "', '".$data['end']."')");
        else 
            echo ("parameters missing");
    }
);

$app->post(
    '/add/task',
    function () {
        $user = auth();
        
        global $vars;
        $data = $vars['data'];
        
        if(isset($data['name']) && isset($data['description']) && isset($data['effort']) && isset($data['assignee']) && isset($data['priority']) && isset($data['project']))
            execQuery("INSERT INTO tasks (name, description, author, effort, assignee, priority, project) VALUES 
                                         ('".$data['name']."', '".$data['description']."', ".$data['id'].", ".$data['effort'].", ".$data['assignee'].", ".$data['priority'].", ".$data['project'].")");
        else 
            echo ("parameters missing");
    }
);

// ##### THE UPDATE API #####

$app->post(
    '/update/user/:uid',
    function ($uid) {
        auth();
        
        global $vars;
        $data = $vars['data'];
        
        $update = "";
        if(isset($data['name']))
            $update .= "name = '" . $data['name'] . "' ";
            
        if(isset($data['mail']))
            $update .= "mail = '" . $data['mail'] . "' ";
        
        if($update != "")
            execQuery("UPDATE user SET ".$update." WHERE id = " . $uid);
        else
            echo ("parameters missing");
    }
);

$app->post(
    '/update/project/:pid',
    function ($pid) {
        $user = auth();
        
        global $vars;
        $data = $vars['data'];
        
        $update = "";
        if(isset($data['name']))
            $update .= "name = '" . $data['name'] . "' ";
            
        if(isset($data['author']))
            $update .= "author = '" . $data['author'] . "' ";
        
        if($update != "")
            execQuery("UPDATE projects SET ".$update." WHERE id = " . $pid . " AND author = " . $user['id']);
        else
            echo ("parameters missing");
    }
);

$app->post(
    '/update/task/:tid',
    function ($tid) {
        $user = auth();
        
        global $vars;
        $data = $vars['data'];
        
        $update = "";
        if(isset($data['taskName']))
            $update .= "name = '" . $data['taskName'] . "' ";
            
        if(isset($data['description']))
            $update .= "description = '" . $data['description'] . "' ";
            
        if(isset($data['effort']))
            $update .= "effort = " . $data['effort'] . " ";
            
        if(isset($data['assignee']))
            $update .= "assignee = " . $data['assignee'] . " ";
            
        if(isset($data['priority']))
            $update .= "priority = " . $data['priority'] . " ";
            
        if(isset($data['project']))
            $update .= "project = " . $data['project'] . " ";
            
        if(isset($data['used']))
            $update .= "used = " . $data['used'] . " ";
        
        if($update != "")
            execQuery("UPDATE tasks SET ".$update." WHERE id = " . $tid);
        else
            echo ("parameters missing");
    }
);

$app->run();

//mysql_free_result($result);
//mysql_close($db);