var newsboard = angular.module('newsboard', []);

var viewModes = {
    newsboard: 'newsboard',
    oauth: 'oauth',
};

newsboard.controller('NewsboardController', ['$scope', '$http', '$interval', function ($scope, $http, $interval) {

    $scope.viewMode = viewModes.newsboard;

    $scope.init = function () {
        $scope.loadData();
        $interval($scope.loadData, 12345);
    };

    $scope.modules = [];

    $scope.groupDateArray = function (list) {
        var day = {};
        var newList = [];
        list.map(function (item) {
            if (item.date != day.date) {
                day = {
                    date: item.date,
                    formattedDate: moment(item.date).calendar(new Date()),
                    hours: [],
                };
                newList.push(day);
            }
            item.first = day.hours.length > 0 && item.hour > day.hours[day.hours.length-1].hour;
            day.hours.push(item);
        });
        return newList;
    };

    $scope.loadData = function () {
        $http.get('./newsboardData.php').then(function (response) {
            var data = response.data;
            if (data.error) {
                if (data.error === 403) {
                    $scope.redirectToOAuth();
                } else {
                    alert(data.error);
                }
                return;
            }

            $scope.modules = [{
                title: 'Raumbuchung',
                days: $scope.groupDateArray(data.data.rooms.map(function (room) {
                    return {
                        date: room.date,
                        left: room.room,
                        middle: room.comment,
                        hour: room.hour,
                    };
                })),
            }, {
                title: 'Supplenzplan',
                days: $scope.groupDateArray(data.data.substitutions.map(function (lesson) {
                    return {
                        date: lesson.date,
                        left: lesson.class,
                        middle: lesson.lastName + ' ' + lesson.firstName,
                        hour: lesson.hour,
                    };
                })),
            }];
        }, function (error) {
            console.log(error);
        });
    };

    $scope.redirectToOAuth = function () {
        $scope.viewMode = viewModes.oauth;
    }

}]);
