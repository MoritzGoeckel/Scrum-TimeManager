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