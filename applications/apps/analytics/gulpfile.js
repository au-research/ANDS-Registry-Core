var gulp = require('gulp');
var useref = require('gulp-useref');
var uglify = require('gulp-uglify');
var gulpIf = require('gulp-if');
var concat = require('gulp-concat');
var gulpgit = require('gulp-git');
var sourcemaps = require('gulp-sourcemaps');
var angularfilesort = require('gulp-angular-filesort');
var inject = require('gulp-inject');

gulp.task('combine_min_nm_js', function(done){

    var src =[
        'node_modules/angular/angular.min.js',
        'node_modules/angular-route/angular-route.min.js',
        'node_modules/Chart.js/Chart.min.js',
        'node_modules/angular-chart.js/angular-chart.js',
        'node_modules/moment/moment.js',
        'node_modules/angular-bootstrap/ui-bootstrap.min.js',
        'node_modules/angular-bootstrap/ui-bootstrap-tpls.min.js',
        'assets/js/daterangepicker.js',
        'node_modules/angular-daterangepicker/js/angular-daterangepicker.js',
        'assets/js/analytics_app.js',
        'assets/js/main_ctrl.js',
        'assets/js/report_ctrl.js',
        'assets/js/analytics_chart_directive.js',
        'assets/js/ro_directive.js',
        'assets/js/analytics_filter_service.js',
        'assets/js/analytics_factory.js',
        'assets/js/analytics_modal_detail_controller.js'
    ];

    return gulp.src(src)
        .pipe(concat("analytics_js_combined.js"))
        .pipe(gulp.dest('assets/dist'));
});

gulp.task('combine_min_app_js', function(done){

    var src =[
        'assets/js/analytics_app.js',
        'assets/js/main_ctrl.js',
        'assets/js/report_ctrl.js',
        'assets/js/analytics_chart_directive.js',
        'assets/js/ro_directive.js',
        'assets/js/analytics_filter_service.js',
        'assets/js/analytics_factory.js',
        'assets/js/analytics_modal_detail_controller.js'
    ];

    return gulp.src(src)
        .pipe(concat('final_app.js'))
        .pipe(uglify())
        .pipe(gulp.dest('assets/dist'));
});