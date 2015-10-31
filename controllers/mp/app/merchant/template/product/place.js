app.register.controller('productCtrl', ['$scope', '$http', '$timeout', 'Product', 'Sku', function($scope, $http, $timeout, Product, Sku) {
	var facProduct, facSku, today, startSku = null;
	facProduct = new Product($scope.$parent.mpid, $scope.$parent.shopId);
	var productGet = function(id) {
		$scope.skuLoading = true;
		facProduct.get(id).then(function(product) {
			var propValue, today, options;
			$scope.product = product;
			$scope.catelog = product.catelog;
			$scope.propValues = product.propValue2;
			facSku = new Sku($scope.$parent.mpid, $scope.$parent.shopId, id);
			options = {
				beginAt: $scope.skuFilter.time.begin,
				endAt: $scope.skuFilter.time.end,
				autogen: 'Y'
			};
			facSku.get(options).then(function(skus) {
				$scope.skus = skus;
				$scope.skuLoading = false;
			})
		});
	};
	today = new Date();
	today.setHours(0, 0, 0, 0);
	today = today.getTime() / 1000;
	$scope.orderInfo = {
		skus: {}
	};
	$scope.skuLoading = false;
	$scope.skuFilter = {
		time: {
			begin: today,
			end: today + 86399
		}
	};
	$scope.prevDay = function() {
		$scope.skuFilter.time.begin -= 86400;
		$scope.skuFilter.time.end -= 86400;
		productGet($scope.$parent.productId);
	};
	$scope.nextDay = function() {
		$scope.skuFilter.time.begin += 86400;
		$scope.skuFilter.time.end += 86400;
		productGet($scope.$parent.productId);
	};
	var chooseSkuSegment = function(start, end) {
		var seg, i, sku;
		seg = new Array(2);
		seg[0] = $scope.skus.indexOf(start);
		seg[1] = $scope.skus.indexOf(end);
		seg[0] > seg[1] && seg.reverse();
		for (i = seg[0] + 1; i < seg[1]; i++) {
			sku = $scope.skus[i];
			if (!sku.selected) {
				sku.selected = true;
				$scope.orderInfo.skus[sku.id] = {
					count: 1
				};
			}
		}
	};
	$scope.chooseSku = function(sku) {
		if (sku.quantity == 0) return;
		sku.selected = !sku.selected;
		if (sku.selected) {
			$scope.orderInfo.skus[sku.id] = {
				count: 1
			};
			if (startSku === null) {
				startSku = sku;
			} else {
				chooseSkuSegment(startSku, sku);
				startSku = null;
			}
		} else {
			delete $scope.orderInfo.skus[sku.id];
		}
	};
	var hammertime = Hammer(document.querySelector('#skus'), {});
	hammertime.on('swipeleft', function(event) {
		$scope.nextDay();
	}).on('swiperight', function(event) {
		$scope.prevDay();
	});
	productGet($scope.$parent.productId);
}]);