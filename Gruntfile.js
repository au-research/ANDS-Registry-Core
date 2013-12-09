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
					'<%=yeoman.assets %>/lib/jquery.sticky.js',
					'<%=yeoman.assets %>/lib/mustache.js',
					'<%=yeoman.assets %>/lib/chosen/chosen.jquery.js',
					'<%=yeoman.assets %>/lib/jquery.ba-hashchange.js',
					'<%=yeoman.assets %>/lib/bootstrap_toggle_button/jquery.toggle.buttons.js',
					'<%=yeoman.assets %>/lib/qtip2/jquery.qtip.min.js',
					'<%=yeoman.assets %>/js/scripts.js',
				],
				dest:'<%=yeoman.assets %>/js/arms.scripts.js'
			},
			portalStyles:{
				options:{separator:''},
				src:[
					'<%=yeoman.portal_assets %>/css/knacss.css',
					'<%=yeoman.portal_assets %>/style.css',
					'<%=yeoman.portal_assets %>/css/ands-theme/jquery-ui-1.10.0.custom.min.css',
					'<%=yeoman.portal_assets %>/css/flexslider.css',
					'<%=yeoman.portal_assets %>/css/ui.dynatree.css',
					'<%=yeoman.portal_assets %>/css/ands.less.compiled.css',
					'<%=yeoman.portal_assets %>/css/ands_portal.css',
					'<%=yeoman.assets %>/lib/qtip2/jquery.qtip.min.css',
					'<%=yeoman.assets %>/lib/jQRangeSlider/css/iThing.css',
				],
				dest: '<%=yeoman.portal_assets %>/ands_portal.combined.css'
			},
			portalScripts:{
				options:{separator:';'},
				src:[
					'<%=yeoman.assets %>/lib/jquery-1.8.3.min.js',
					'<%=yeoman.portal_assets %>/js/jquery.flexslider-min.js',
					'<%=yeoman.assets %>/lib/jquery-ui-1.8.23.custom.min.js',
					'<%=yeoman.assets %>/lib/qtip2/jquery.qtip.min.js',
					'<%=yeoman.assets %>/lib/jQRangeSlider/jQAllRangeSliders-withRuler-min.js',
					'<%=yeoman.assets %>/lib/less-1.3.0.min.js',
					'<%=yeoman.assets %>/lib/typeahead.min.js',
					'<%=yeoman.assets %>/lib/mustache.js',
					'<%=yeoman.assets %>/lib/jquery.ba-hashchange.min.js',
					'<%=yeoman.portal_assets %>/js/script.js',
				],
				dest: '<%=yeoman.portal_assets %>/ands_portal.combined.js'
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
			},
			portal:{
				options: {
					sassDir: '<%= yeoman.portal_assets %>/sass',
					cssDir: '<%= yeoman.portal_assets %>/css',
					debugInfo: false
				}
			}
		},
		less: {
			core:{
				files:{
					"<%= yeoman.assets %>/css/arms.less.compiled.css": "<%= yeoman.assets %>/less/arms.less"
				}
			},
			portal:{
				files:{
					"<%= yeoman.portal_assets %>/css/ands.less.compiled.css": "<%= yeoman.portal_assets %>/less/ands.less"
				}
			}
		},
		watch: {
			core: {
				files: ['<%= yeoman.assets %>/sass/{,*/}*.{scss,sass}', '<%= yeoman.assets %>/less/{,*/}*.{less,less}'],
				tasks: ['compass:core', 'concat:coreStyles', 'cssmin'],
				options:{nospawn:true}
			},
			portal:{
				files: ['<%= yeoman.portal_assets %>/sass/{,*/}*.{scss,sass}', '<%= yeoman.portal_assets %>/less/{,*/}*.{less,less}'],
				tasks: ['compass:portal', 'less:portal', 'concat:portalStyles'],
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

	grunt.registerTask('portal', [
		'compass:portal',
		'less:portal',
		'concat:portalStyles',
		'concat:portalScripts'
	]);
}