module.exports = function (grunt) {

  //configuration goes here
  var yeomanConfig = {
    assets: 'core/assets',
    templates: 'templates',


  }

  grunt.initConfig({
    yeoman: yeomanConfig,
    cssmin: {
      options: {report: 'min'},
      minify: {
        src: '<%= yeoman.assets %>/css/portal.combine.css',
        dest: '<%= yeoman.assets %>/css/portal.combine.min.css'
      }
    },
    concat: {
      styles: {
        options: {separator: ''},
        src: [
          '<%= yeoman.templates %>/ands-green/assets/js/neo4jd3/css/font-awesome.min.css',
          '<%= yeoman.templates %>/ands-green/assets/js/neo4jd3/css/neo4jd3.css',
          '<%= yeoman.templates %>/ands-green/assets/css/bootstrap.css',
          '<%= yeoman.templates %>/ands-green/assets/css/theme.css',
          '<%= yeoman.templates %>/ands-green/assets/css/swatch-gray.css',
          '<%= yeoman.templates %>/ands-green/assets/css/swatch-black.css',
          '<%= yeoman.templates %>/ands-green/assets/css/swatch-ands-green.css',
          '<%= yeoman.templates %>/ands-green/assets/css/fonts.min.css',

          '<%= yeoman.assets %>/vendor/dynatree/src/skin/ui.dynatree.css',
          '<%= yeoman.assets %>/vendor/qtip2/jquery.qtip.css',

          '<%= yeoman.assets %>/lib/angular-loading-bar/build/loading-bar.min.css',

          '<%= yeoman.templates %>/ands-green/js/neo4jd3/css/font-awesome.min.css',
          '<%= yeoman.templates %>/ands-green/js/neo4jd3/css/neo4jd3.css',
          '<%= yeoman.templates %>/ands-green/assets/css/ands.css',
          '<%= yeoman.assets %>/css/portal.less.compiled.css'
        ],
        dest: '<%= yeoman.assets %>/css/portal.prepurify.combine.css'
      },
      lib: {
        options: {separator: ';'},
        src: [
          '<%=yeoman.templates %>/ands-green/assets/js/packages.min.js',
          '<%=yeoman.assets %>/vendor/qtip2/jquery.qtip.js',
          '<%=yeoman.assets %>/vendor/mustache/mustache.min.js',
          '<%=yeoman.assets %>/vendor/colorbox/jquery.colorbox-min.js',
          '<%=yeoman.assets %>/lib/angular/angular.min.js',
          '<%=yeoman.assets %>/lib/angular-route/angular-route.min.js',
          '<%=yeoman.assets %>/lib/angular-sanitize/angular-sanitize.min.js',
          '<%=yeoman.assets %>/lib/angular-animate/angular-animate.min.js',
          '<%=yeoman.assets %>/lib/angular-ui-utils/ui-utils.min.js',
          '<%=yeoman.assets %>/lib/angular-bootstrap/ui-bootstrap.min.js',
          '<%=yeoman.assets %>/lib/angular-bootstrap/ui-bootstrap-tpls.min.js',
          '<%=yeoman.assets %>/lib/angular-loading-bar/build/loading-bar.min.js',
          '<%=yeoman.assets %>/lib/angular-google-maps/dist/angular-google-maps.js',
          '<%=yeoman.assets %>/lib/angular-lz-string/angular-lz-string.js',
          '<%=yeoman.assets %>/vendor/lodash/dist/lodash.min.js',
          '<%=yeoman.templates %>/ands-green/assets/js/d3.min.js',
          '<%=yeoman.templates %>/ands-green/assets/js/neo4jd3/js/neo4jd3.js'
        ],
        dest: '<%=yeoman.assets %>/js/lib.js'
      },
      portal_lib: {
        options: {separator: ';'},
        src: [
          '<%=yeoman.assets %>/js/scripts.js',
          'registry_object/assets/js/record_components.js',
          'profile/assets/js/profile_components.js',
          'registry_object/assets/js/search.js',
          'registry_object/assets/js/portal-filters.js',
          'registry_object/assets/js/query_builder.js',
          'registry_object/assets/js/portal-directives.js',
          'registry_object/assets/js/vocab-factory.js',
          'registry_object/assets/js/search_controller.js',
          'registry_object/assets/js/search-factory.js'
        ],
        dest: '<%=yeoman.assets %>/js/portal_lib.js'
      }
    },
    uglify: {
      options: {
        mangle: false,
        report: 'min'
      },
      scripts: {
        files: {
          '<%= yeoman.assets %>/js/lib.min.js': ['<%= yeoman.assets %>/js/lib.js'],
          '<%= yeoman.assets %>/js/portal_lib.min.js': ['<%= yeoman.assets %>/js/portal_lib.js'],
        }
      }
    },
    less: {
      core: {
        files: {
          '<%= yeoman.assets %>/css/portal.less.compiled.css': '<%= yeoman.templates %>/ands-green/assets/less/ands-portal.less',
          '<%= yeoman.assets %>/css/print.css': '<%= yeoman.templates %>/ands-green/assets/less/print.less'
        }
      }
    },
    purifycss: {
        options: {},
        target: {
            src: ['<%= yeoman.templates %>/ands-green/includes/*.php',
                '<%= yeoman.templates %>/ands-green/layouts/*.php',
                '<%= yeoman.templates %>/ands-green/registry_object/*.php',
                '<%= yeoman.templates %>/ands-green/registry_object/contents/*.php',
            ],
            css: ['<%= yeoman.assets %>/css/portal.prepurify.combine.css'],
            dest: '<%=yeoman.assets %>/css/portal.combine.css'
        },
    },
    watch: {
      styles: {
        files: ['**/*.less', '**/*.css', '!core/assets/dist/*.css'],
        tasks: ['less', 'concat:styles', 'clean', 'purifycss', 'assets_versioning']
      },
      scripts: {
        files: [
          'registry_object/**/*.js',
          'profile/**/*.js',
          'core/assets/js/*.js',
          '!core/assets/dist/*.js'
        ],
        tasks: ['concat:lib', 'concat:portal_lib', 'clean', 'purifycss', 'assets_versioning']
      }
    },
    clean: ['<%=yeoman.assets %>/dist'],
    assets_versioning: {
      css: {
        options: {
          tag: 'hash',
          versionsMapFile: '<%=yeoman.assets %>/dist/manifest.json',
          versionsMapTrimPath: 'core/assets/dist/'
        },
        files: {
          '<%=yeoman.assets %>/dist/lib.js': ['<%=yeoman.assets %>/js/lib.js'],
          '<%=yeoman.assets %>/dist/portal_lib.js': ['<%=yeoman.assets %>/js/portal_lib.js'],
          '<%=yeoman.assets %>/dist/portal.combine.css': ['<%=yeoman.assets %>/css/portal.combine.css']
        }
      }
    },
    copy: {
      main: {
        files: [
          {
            expand:true,
            cwd: '<%=yeoman.assets %>/css/',
            src: '*.gif',
            dest: '<%=yeoman.assets %>/dist/',
            filter: 'isFile'
          }
        ]
      }
    },
  })
  require('load-grunt-tasks')(grunt);

  grunt.registerTask('default', ['less', 'concat', 'clean', 'assets_versioning', 'copy' , 'purifycss'])
  grunt.registerTask('dev', ['default'])
  grunt.registerTask('prod', ['default', 'uglify']);
}
