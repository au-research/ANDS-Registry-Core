module.exports = function(grunt){
	//configuration goes here
	var yeomanConfig = {
		assets: 'core/assets',
		templates: 'templates'
	}
	grunt.initConfig({
		yeoman: yeomanConfig,
		concurrent:{
			dev:['watch:compass']
		},
		cssmin:{
			options:{
				report:'min'
			},
			minify:{
				src:'<%= yeoman.assets %>/css/arms.combine.css',
				dest:'<%= yeoman.assets %>/css/arms.comb.min.css'
			}
		},
		concat:{
			styles:{
				options:{separator:''},
				src:[
					'<%= yeoman.templates %>/omega/assets/css/bootstrap.min.css',
					'<%= yeoman.templates %>/omega/assets/css/theme.css',
					'<%= yeoman.templates %>/omega/assets/css/swatch-gray.css',
					'<%= yeoman.templates %>/omega/assets/css/swatch-black.min.css',
					'<%= yeoman.templates %>/omega/assets/css/swatch-ands-green.css',
					'<%= yeoman.templates %>/omega/assets/css/fonts.min.css',

					'<%= yeoman.assets %>/lib/dynatree/src/skin/ui.dynatree.css',
					'<%= yeoman.assets %>/lib/qtip2/jquery.qtip.css',
					'<%= yeoman.assets %>/lib/angular-loading-bar/build/loading-bar.min.css',

					'<%= yeoman.templates %>/omega/assets/css/ands.css',
					'<%= yeoman.assets %>/css/portal.less.compiled.css',
				],
				dest: '<%= yeoman.assets %>/css/portal.combine.css'
			},
			lib:{
				options:{separator:';'},
				src:[
					'<%=yeoman.assets %>/lib/angular/angular.min.js',
					'<%=yeoman.assets %>/lib/angular-route/angular-route.min.js',
					'<%=yeoman.assets %>/lib/angular-sanitize/angular-sanitize.min.js',
					'<%=yeoman.assets %>/lib/angular-animate/angular-animate.min.js',
					'<%=yeoman.assets %>/lib/angular-ui-utils/ui-utils.min.js',
					'<%=yeoman.assets %>/lib/angular-bootstrap/ui-bootstrap.min.js',
					'<%=yeoman.assets %>/lib/angular-bootstrap/ui-bootstrap-tpls.min.js',
					'<%=yeoman.assets %>/lib/angular-loading-bar/build/loading-bar.min.js',
					'<%=yeoman.templates %>/omega/js/packages.min.js',
					'<%=yeoman.templates %>/lib/qtip2/jquery.qtip.js',
				],
				dest:'<%=yeoman.assets %>/js/lib.js'
			},
			portal_lib: {
				options:{separator:';'},
				src:[
					'<%=yeoman.assets %>/lib/lodash/dist/lodash.min.js',
					'<%=yeoman.assets %>/lib/angular-google-maps/dist/angular-google-maps.js',
					'<%=yeoman.assets %>/lib/angular-lz-string/angular-lz-string.js',
					'registry_object/assets/js/record_components.js',
					'profile/assets/js/profile_components.js',
					'registry_object/assets/js/search.js',
					'registry_object/assets/js/portal-filters.js',
					'registry_object/assets/js/query_builder.js',
					'registry_object/assets/js/portal-directives.js',
					'registry_object/assets/js/vocab-factory.js',
					'registry_object/assets/js/search_controller.js',
					'registry_object/assets/js/search-factory.js',
				],
				dest:'<%=yeoman.assets %>/js/portal_lib.js'
			}
		},
		uglify:{
			options:{
				mangle: false,
				report: 'min'
			},
			scripts:{
				files:{
					'<%= yeoman.assets %>/js/arms.min.js':['<%= yeoman.assets %>/js/arms.scripts.js']
				}
			}
		},
		less: {
			core:{
				files:{
					"<%= yeoman.assets %>/css/portal.less.compiled.css": "<%= yeoman.templates %>/omega/assets/less/ands-portal.less"
				}
			}
		},
		watch: {
			core: {
				files: ['<%= yeoman.assets %>/sass/{,*/}*.{scss,sass}', '<%= yeoman.assets %>/less/{,*/}*.{less,less}'],
				tasks: ['compass:core', 'concat:coreStyles', 'cssmin'],
				options:{nospawn:true}
			}
		}
	});
	require('load-grunt-tasks')(grunt);

	//define your tasks
	grunt.registerTask('default', [
		'compass',
		'less',
		'concat',
		'uglify'
	]);

	grunt.registerTask('core',[
		'watch:core'
	]);

}