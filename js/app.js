var app = angular.module("app", ['ngRoute', 'ngCookies']);

app.config(['$routeProvider',
 function($routeProvider) {
    $routeProvider.
       when('/login', {
          templateUrl: 'login.htm',
          controller: 'loginCtl'
       }).
       when('/projects', {
          templateUrl: 'projects.htm',
          controller: 'projectsCtl'
       }).
       when('/project/:id', {
          templateUrl: 'project.htm',
          controller: 'projectCtl'
       }).
       when('/task/:id', {
          templateUrl: 'task.htm',
          controller: 'taskCtl'
       }).
       when('/sprint/:id', {
          templateUrl: 'sprint.htm',
          controller: 'sprintCtl'
       }).
       when('/user/:id', {
          templateUrl: 'user.htm',
          controller: 'userCtl'
       }).
       otherwise({
          redirectTo: '/login'
       });
 }]);

function doLogin($rootScope, $cookies, $location){
    if($cookies['auth'] == null){
        $location.path( "/login" );
        return;
    }
    
    $rootScope.auth = JSON.parse($cookies['auth']);
    $rootScope.user = JSON.parse($cookies['user']);
}

 app.controller('loginCtl', function($scope, $http, $rootScope, $location, $cookies) {
    $scope.message = "LOGIN";
    if($cookies['auth'] != null){
        $rootScope.auth = JSON.parse($cookies['auth']);
        $location.path( "/projects" );
    }
    else{
        $scope.submit = function() {
            $http.post('api.php/login/'+ $scope.name +'/'+ $scope.pw).                  
              then(function(response) {
                    $rootScope.user = response.data[0];
                    $rootScope.auth = {uid: $rootScope.user.id, secret: $rootScope.user.secret};
                    $cookies['auth'] = JSON.stringify($rootScope.auth);
                    $cookies['user'] = JSON.stringify($rootScope.user);
                    $location.path( "/projects" );
              }, function(response) {console.log("Error: " + response);});
      };
    }
 });

 app.controller('projectsCtl', function($scope, $http, $rootScope, $location, $cookies) {
    doLogin($rootScope, $cookies, $location)
    
    $http.post('api.php/user/' + $rootScope.user.id + '/projects/', $rootScope.auth).                  
        then(function(response) 
        {
            $rootScope.projects = response.data;
        }, 
        function(response) {console.log("Error: " + response);});
});

 app.controller('projectCtl', function($scope, $http, $rootScope, $location, $cookies, $routeParams) {
    doLogin($rootScope, $cookies, $location)
    
    $scope.id = $routeParams.id;
    
    $http.post('api.php/project/' + $routeParams.id, $rootScope.auth).                  
        then(function(response) 
        {
            $scope.project = response.data[0];
        }, 
        function(response) {console.log("Error: " + response);});
    
    $http.post('api.php/project/' + $routeParams.id + '/tasks', $rootScope.auth).                  
        then(function(response) 
        {
            $scope.tasks = response.data;
        }, 
        function(response) {console.log("Error: " + response);});
        
    $http.post('api.php/project/' + $routeParams.id + '/sprints', $rootScope.auth).                  
        then(function(response) 
        {
            $scope.sprints = response.data;
        }, 
        function(response) {console.log("Error: " + response);});
});

 app.controller('taskCtl', function($scope, $http, $rootScope, $location, $cookies, $routeParams) {
        doLogin($rootScope, $cookies, $location)
        
        $scope.id = $routeParams.id;
        
        $http.post('api.php/task/' + $routeParams.id, $rootScope.auth).                  
            then(function(response) 
            {
                $scope.task = response.data[0];
            }, 
            function(response) {console.log("Error: " + response);});
});

 app.controller('sprintCtl', function($scope, $http, $rootScope, $location, $cookies, $routeParams) {
        doLogin($rootScope, $cookies, $location)
        
        $scope.id = $routeParams.id;
        
        $http.post('api.php/sprint/' + $routeParams.id, $rootScope.auth).                  
            then(function(response) 
            {
                $scope.sprint = response.data[0];
            }, 
            function(response) {console.log("Error: " + response);});
        
        $http.post('api.php/sprint/' + $routeParams.id + '/tasks/', $rootScope.auth).                  
            then(function(response) 
            {
                $scope.tasks = response.data;
            }, 
            function(response) {console.log("Error: " + response);}); 
            
});

 app.controller('userCtl', function($scope, $http, $rootScope, $location, $cookies, $routeParams) {
        doLogin($rootScope, $cookies, $location)
        
        $scope.id = $routeParams.id;
        
        $http.post('api.php/user/' + $routeParams.id, $rootScope.auth).                  
            then(function(response) 
            {
                $scope.user = response.data[0];
            }, 
            function(response) {console.log("Error: " + response);});
});