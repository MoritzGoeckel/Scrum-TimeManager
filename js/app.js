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

 app.controller('loginCtl', function($scope) {
    $scope.message = "LOGIN";
 });

 app.controller('dashboardCtl', function($scope) {
    $scope.message = "DAHBOARD";
 });