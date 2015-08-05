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

 app.controller('loginCtl', function($scope, $http, $rootScope) {
    $scope.message = "LOGIN";
    $scope.submit = function() {
        $http.get('api.php/login/'+ $scope.name +'/'+ $scope.pw).                  
          then(function(response) {
            $rootScope.user = response.data[0];
          }, function(response) {console.log("Error: " + response);});
      };
 });

 app.controller('dashboardCtl', function($scope, $rootScope) {
    $scope.user = $rootScope.user;
 });