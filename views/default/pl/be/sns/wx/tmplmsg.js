define(['main'], function(ngApp) {
	'use strict';
	ngApp.provider.service('serTmplmsg', ['$q', 'http2', function($q, http2) {
		var _baseURL = '/rest/pl/fe/matter/tmplmsg';
		this.list = function() {
			var defer = $q.defer();
			http2.get(_baseURL + '/list?site=platform&cascaded=Y', function(rsp) {
				defer.resolve(rsp.data);
			});
			return defer.promise;
		};
		this.create = function() {
			var defer = $q.defer();
			http2.get(_baseURL + '/create?site=platform', function(rsp) {
				defer.resolve(rsp.data);
			});
			return defer.promise;
		};
		this.update = function(id, data) {
			var defer = $q.defer();
			http2.post(_baseURL + '/update?site=platform&id=' + id, data, function(rsp) {
				defer.resolve(rsp.data);
			});
			return defer.promise;
		};
		this.remove = function(id) {
			var defer = $q.defer();
			http2.get(_baseURL + '/remove?site=platform&id=' + id, function(rsp) {
				defer.resolve(rsp.data);
			});
			return defer.promise;
		};
		this.addParam = function(tmplmsgId) {
			var defer = $q.defer();
			http2.get(_baseURL + '/addParam?site=platform&tid=' + tmplmsgId, function(rsp) {
				defer.resolve(rsp.data);
			});
			return defer.promise;
		};
		this.updateParam = function(paramId, updated) {
			var defer = $q.defer();
			http2.post(_baseURL + '/updateParam?site=platorm&id=' + paramId, updated, function(rsp) {
				defer.resolve(rsp.data);
			});
			return defer.promise;
		};
		this.removeParam = function(removed) {
			var defer = $q.defer();
			http2.get(_baseURL + '/removeParam?site=platform&pid=' + removed.id, function(rsp) {
				defer.resolve(rsp.data);
			});
			return defer.promise;
		};
	}]);
	ngApp.provider.controller('ctrlTmplmsg', ['$scope', 'serTmplmsg', function($scope, serTmplmsg) {
		$scope.create = function() {
			serTmplmsg.create().then(function(data) {
				$scope.tmplmsgs.push(data);
			});
		};
		$scope.edit = function(tmplmsg) {
			$scope.editing = tmplmsg;
		};
		$scope.doSearch = function() {
			serTmplmsg.list().then(function(data) {
				$scope.tmplmsgs = data;
			});
		};
		$scope.update = function(n) {
			if (!angular.equals($scope.editing, $scope.persisted)) {
				var nv = {};
				nv[n] = $scope.editing[n];
				serTmplmsg.update($scope.editing.id, nv).then(function() {
					$scope.persisted = angular.copy($scope.editing);
				});
			}
		};
		$scope.remove = function() {
			serTmplmsg.remove($scope.editing.id).then(function() {
				var i = $scope.tmplmsgs.indexOf($scope.editing);
				$scope.tmplmsgs.splice(i, 1);
				$scope.editing = null;
			});
		};
		$scope.addParam = function() {
			serTmplmsg.addParam($scope.editing.id).then(function(data) {
				var oNewParam = {
					id: data,
					pname: 'keyword',
					plabel: ''
				};
				!angular.isArray($scope.editing.params) && ($scope.editing.params = []);
				$scope.editing.params.push(oNewParam);
			});
		};
		$scope.updateParam = function(updated, name) {
			var p = {
				pname: updated.pname,
				plabel: updated.plabel,
			};
			serTmplmsg.updateParam(updated.id, p);
		};
		$scope.removeParam = function(removed) {
			serTmplmsg.removeParam(removed).then(function() {
				var params = $scope.editing.params;
				params.splice(params.indexOf(removed), 1);
			});
		};
		$scope.doSearch();
	}]);
	ngApp.provider.controller('sendCtrl', ['$rootScope', '$scope', 'http2', '$uibModal', function($rootScope, $scope, http2, $uibModal) {
		$scope.matterTypes = [{
			value: 'article',
			title: '单图文',
			url: '/rest/pl/fe/matter/tmplmsg'
		}, {
			value: 'news',
			title: '多图文',
			url: '/rest/pl/fe/matter/tmplmsg'
		}, {
			value: 'channel',
			title: '频道',
			url: '/rest/pl/fe/matter/tmplmsg'
		}, ];
		$scope.userSet = [];
		$scope.data = {};
		$scope.matter = null;
		$scope.startUserPicker = function() {
			$uibModal.open({
				templateUrl: 'userPicker.html',
				controller: 'userPickerCtrl',
				backdrop: 'static',
				size: 'lg',
				windowClass: 'auto-height'
			}).result.then(function(data) {
				$scope.userSet = data.userSet;
				$scope.targetUser = data.targetUser;
			});
		};
		$scope.startMatterPicker = function() {
			$scope.$broadcast('mattersgallery.open', function(aSelected, matterType) {
				if (aSelected.length) {
					$scope.matter = {};
					$scope.matter = aSelected[0];
					$scope.matter.type = matterType;
				}
			});
		};
		$scope.removeMatter = function() {
			$scope.message.matter = null;
		};
		$scope.send = function() {
			var posted, url;
			posted = {
				data: $scope.data,
				url: $scope.url,
				userSet: $scope.userSet
			};
			if ($scope.matter) posted.matter = $scope.matter;
			url = '/rest/mp/send/tmplmsg';
			url += '?tid=' + $scope.editing.id;
			http2.post(url, posted, function(rsp) {
				$rootScope.infomsg = '发送完成';
			});
		};
	}]);
	ngApp.provider.controller('logCtrl', ['$scope', 'http2', function($scope, http2) {
		$scope.page = {
			at: 1,
			size: 30
		};
		$scope.doSearch = function() {
			var url = '/rest/mp/send/tmplmsglog?tid=' + $scope.editing.id;
			url += '&page=' + $scope.page.at + '&size=' + $scope.page.size;
			http2.get(url, function(rsp) {
				$scope.logs = rsp.data.logs;
				$scope.page.total = rsp.data.total;
			});
		};
		$scope.doSearch();
	}])
});