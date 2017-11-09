'use strict';

var app = angular.module('app');

app.service('EventService', function($http) {
    this.get = function() {
        return $http.get('/api');
    };
    this.post = function (data) {
        return $http.post('/api', data);
    };
    this.put = function (id, data) {
        return $http.put('/api/' + id, data);
    };
    this.delete = function (id) {
        return $http.delete('/api/' + id);
    };
});