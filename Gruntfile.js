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
		watch: {
			core: {
				files: ['<%= yeoman.assets %>/sass/{,*/}*.{scss,sass}'],
				tasks: ['compass:core'],
				options:{nospawn:true}
			},
			portal:{
				files: ['<%= yeoman.portal_assets %>/sass/{,*/}*.{scss,sass}'],
				tasks: ['compass:portal'],
				options:{nospawn:true}
			}
		}
	});
	require('load-grunt-tasks')(grunt);

	//define your tasks
	grunt.registerTask('default', [
		'compass',
	]);

	grunt.registerTask('core',[
		'watch:core'
	]);

	grunt.registerTask('portal', [
		'watch:portal'
	]);
}