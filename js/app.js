var app = angular.module("app", ['ngRoute']);

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

 app.controller('loginCtl', function($scope, $http, $rootScope) {
    $scope.message = "LOGIN";
    $scope.submit = function() {
        $http.post('api.php/login/'+ $scope.name +'/'+ $scope.pw).                  
          then(function(response) {
            $rootScope.user = response.data[0];
            $rootScope.auth = {uid: $rootScope.user.id, secret: $rootScope.user.secret};
          }, function(response) {console.log("Error: " + response);});
      };
 });

 app.controller('projectsCtl', function($scope, $http, $rootScope) {
     console.log($rootScope.auth);
    $http.post('api.php/user/' + $rootScope.user.id + '/projects/', $rootScope.auth).                  
          then(function(response) {
            console.log('api.php/user/' + $rootScope.user.id + '/projects/');
            console.log(response.data);
            $rootScope.projects = response.data;
          }, function(response) {console.log("Error: " + response);});
 });