var app = angular.module("app", ['ngRoute']);
      
app.config(['$routeProvider',
 function($routeProvider) {
    $routeProvider.
       when('/login', {
          templateUrl: 'login.htm',
          controller: 'loginCtl'
       }).
       when('/dashboard', {
          templateUrl: 'dashboard.htm',
          controller: 'dashboardCtl'
       }).
       otherwise({
          redirectTo: '/login'
       });
 }]);

 app.controller('loginCtl', function($scope, $http) {
    $scope.message = "LOGIN";
    $scope.submit = function() {
        console.log('api.php/login/'+ $scope.name +'/'+ $scope.pw);
        $http.get('api.php/login/'+ $scope.name +'/'+ $scope.pw).                  
          then(function(response) {
            console.log(response);
          }, function(response) {console.log("Error: " + response);});
      };
 });

 app.controller('dashboardCtl', function($scope) {
    $scope.message = "DAHBOARD";
 });