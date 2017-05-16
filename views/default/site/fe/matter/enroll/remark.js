'use strict';
require('./remark.css');

var ngApp = require('./main.js');
ngApp.controller('ctrlRemark', ['$scope', '$q', '$http', function($scope, $q, $http) {
    function listRemarks(schema) {
        var url, defer = $q.defer();
        url = '/rest/site/fe/matter/enroll/remark/list?site=' + oApp.siteid + '&ek=' + ek;
        schema && (url += '&schema=' + schema.id);
        $http.get(url).success(function(rsp) {
            defer.resolve(rsp.data)
        });
        return defer.promise;
    }

    function summary() {
        var url, defer = $q.defer();
        url = '/rest/site/fe/matter/enroll/remark/summary?site=' + oApp.siteid + '&ek=' + ek;
        $http.get(url).success(function(rsp) {
            defer.resolve(rsp.data)
        });
        return defer.promise;
    }
    var oApp, ek, enterSchemaId, schemaRemarks, remarkableSchemas = [];
    ek = location.search.match(/[\?&]ek=([^&]*)/)[1];
    enterSchemaId = location.search.match(/[\?&]schema=([^&]*)/)[1];
    $scope.newRemark = {};
    $scope.schemaRemarks = schemaRemarks = {};
    $scope.switchSchema = function(schema) {
        schema._open = !schema._open;
        if (schema._open) {
            listRemarks(schema).then(function(result) {
                schemaRemarks[schema.id] = result.remarks;
            })
        }
    };
    $scope.addRemark = function(schema) {
        var url;
        url = '/rest/site/fe/matter/enroll/remark/add?site=' + oApp.siteid + '&ek=' + ek;
        schema && (url += '&schema=' + schema.id);
        $http.post(url, $scope.newRemark).success(function(rsp) {
            if (schema) {
                !schemaRemarks[schema.id] && (schemaRemarks[schema.id] = []);
                schemaRemarks[schema.id].splice(0, 0, rsp.data);
            } else {
                $scope.remarks.splice(0, 0, rsp.data);
            }
            $scope.newRemark.content = '';
        });
    };
    $scope.likeRemark = function(oRemark) {
        var url;
        url = '/rest/site/fe/matter/enroll/remark/like';
        url += '?site=' + oApp.siteid;
        url += '&remark=' + oRemark.id;
        $http.get(url).success(function(rsp) {
            oRemark.like_log = rsp.data.like_log;
            oRemark.like_num = rsp.data.like_num;
        });
    };
    $scope.$on('xxt.app.enroll.ready', function(event, params) {
        oApp = params.app;
        $scope.record = params.record;
        summary().then(function(result) {
            var summaryBySchema = {};
            result.forEach(function(schema) {
                summaryBySchema[schema.schema_id] = schema;
            });
            oApp.dataSchemas.forEach(function(schema) {
                if (schema.remarkable === 'Y') {
                    summaryBySchema[schema.id] && (schema.summary = summaryBySchema[schema.id]);
                    schema._open = false;
                    remarkableSchemas.push(schema);
                    if (enterSchemaId === schema.id) {
                        $scope.switchSchema(schema);
                    }
                }
            });
            $scope.remarkableSchemas = remarkableSchemas;
        });
    });
}]);
