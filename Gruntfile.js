module.exports = function(grunt){
	//configuration goes here
	var yeomanConfig = {
		assets: 'assets',
		base: '../../assets'
	}
	grunt.initConfig({
		yeoman: yeomanConfig,
		concurrent:{
			dev:['watch:compass']
		},
		compass: {
			dev:{
				options: {
					sassDir: '<%= yeoman.assets %>/sass',
					cssDir: '<%= yeoman.assets %>/css',
					debugInfo: false
				}
			}
		},
		watch: {
			compass: {
				files: ['<%= yeoman.assets %>/sass/{,*/}*.{scss,sass}'],
				tasks: [
					'compass:dev', 
					//'concat:styles', 'cssmin'
				],
				options:{nospawn:true}
			}
		}
	});
	require('load-grunt-tasks')(grunt);

	//define your tasks
	grunt.registerTask('default', [
		'compass',
	]);

	grunt.registerTask('dev',[
		'watch:compass'
	]);
}