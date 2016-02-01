require.config({
	paths: {
		"domReady": '/static/js/domReady',
		"angular": "/static/js/angular.min",
		"angular-sanitize": "/static/js/angular-sanitize.min",
	},
	shim: {
		"angular": {
			exports: "angular"
		},
		"angular-sanitize": {
			deps: ['angular'],
			exports: "angular-sanitize"
		},
	},
	deps: ['/test/loading/app.js?_=9']
});