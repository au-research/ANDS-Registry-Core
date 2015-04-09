module.exports = function(grunt){
	//configuration goes here
	var yeomanConfig = {
		assets: 'assets',
		portal_assets: 'applications/portal/core/assets'
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
			coreStyles:{
				options:{separator:''},
				src:[
					'<%= yeoman.assets %>/lib/twitter_bootstrap/css/bootstrap.css',
					'<%= yeoman.assets %>/lib/twitter_bootstrap/css/bootstrap-responsive.css',
					'<%= yeoman.assets %>/lib/qtip2/jquery.qtip.min.css',
					'<%= yeoman.assets %>/lib/chosen/chosen.css',
					'<%= yeoman.assets %>/lib/bootstrap_toggle_button/jquery.toggle.buttons.css',
					'<%= yeoman.assets %>/lib/unicorn_styles/css/uniform.css',
					'<%= yeoman.assets %>/lib/unicorn_styles/css/unicorn.main.css',
					'<%= yeoman.assets %>/lib/unicorn_styles/css/unicorn.grey.css',
					'<%= yeoman.assets %>/css/base.css',
					'<%= yeoman.assets %>/css/arms.css',
					'<%= yeoman.assets %>/css/arms.less.compiled.css',
				],
				dest: '<%= yeoman.assets %>/css/arms.combine.css'
			},
			coreScripts:{
				options:{separator:';'},
				src:[
					'<%=yeoman.assets %>/lib/less-1.3.0.min.js',
					'<%=yeoman.assets %>/lib/jquery-1.7.2.min.js',
					'<%=yeoman.assets %>/lib/jquery-ui-1.8.22.custom.min.js',
					'<%=yeoman.assets %>/lib/bootstrap.youtubepopup.min.js',
					'<%=yeoman.assets %>/lib/jquery.sticky.js',
					'<%=yeoman.assets %>/lib/mustache.js',
					'<%=yeoman.assets %>/lib/chosen/chosen.jquery.js',
					'<%=yeoman.assets %>/lib/jquery.ba-hashchange.js',
					'<%=yeoman.assets %>/lib/bootstrap_toggle_button/jquery.toggle.buttons.js',
					'<%=yeoman.assets %>/lib/qtip2/jquery.qtip.min.js',
					'<%=yeoman.assets %>/js/scripts.js',
				],
				dest:'<%=yeoman.assets %>/js/arms.scripts.js'
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
		compass: {
			core:{
				options: {
					sassDir: '<%= yeoman.assets %>/sass',
					cssDir: '<%= yeoman.assets %>/css',
					debugInfo: false
				}
			}
		},
		less: {
			core:{
				files:{
					"<%= yeoman.assets %>/css/arms.less.compiled.css": "<%= yeoman.assets %>/less/arms.less"
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