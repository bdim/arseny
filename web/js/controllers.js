'use strict';

var controllers = angular.module('controllers', []);

controllers.controller('EventController', ['$scope', 'EventService',
    function ($scope, EventService) {
        $scope.events = [];
        EventService.get().then(function (data) {
            if (data.status == 200)
                $scope.events = data.data;
        }, function (err) {
            console.log(err);
        })
    }
]);