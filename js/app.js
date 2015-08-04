var app = angular.module("app", ['ngRoute']);
      
app.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
           when('/login', {
              templateUrl: 'login',
              controller: 'loginCtl'
           }).
           when('/dashboard', {
              templateUrl: 'dashboard',
              controller: 'dashboardCtl'
           }).
           otherwise({
              redirectTo: '/dashboard'
           });
    }]);
    
app.controller('loginCtl', function($scope) {
    $scope.message = "-> loginCtl";
});

app.controller('dashboardCtl', function($scope) {
    $scope.message = "-> dashboardCtl";
});