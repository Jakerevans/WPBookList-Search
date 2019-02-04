/**
Mostly derived from https://bitsofco.de/a-simple-gulp-workflow
npm install gulp
npm install --save-dev gulp-sass
npm install --save-dev gulp-concat
npm install --save-dev gulp-uglify
npm install --save-dev gulp-util
npm install --save-dev gulp-rename
npm install --save-dev gulp-babel
npm install --save-dev gulp-zip
npm install --save-dev del
 */

// First require gulp.
var gulp   = require( 'gulp' ),
	sass   = require( 'gulp-sass' ),
	concat = require( 'gulp-concat' ),
	uglify = require( 'gulp-uglify' ),
	gutil  = require( 'gulp-util' ),
	rename = require( 'gulp-rename' ),
	zip    = require( 'gulp-zip' ),
	del    = require( 'del' );

// Define file sources.
var sassFrontendSource        = [ 'dev/scss/wpbooklist-search-main-frontend.scss' ];
var sassFrontendSourcePartial = [ 'dev/scss/_wpbooklist-search-frontend-ui.scss' ];
var sassBackendSource         = [ 'dev/scss/wpbooklist-search-main-admin.scss' ];
var sassBackendSourcePartial  = [ 'dev/scss/_wpbooklist-search-backend-ui.scss' ];
var sassWatch                 = [ 'dev/scss/*.scss' ];
var jsBackendSource           = [ 'dev/js/backend/*.js' ];
var jsFrontendSource          = [ 'dev/js/frontend/*.js' ];
var jsFrontendWatch           = [ 'dev/js/frontend/*.js' ];
var jsBackendWatch            = [ 'dev/js/backend/*.js' ];

// Define default task.
gulp.task( 'default', function() {

});

// Task to compile Frontend SASS file.
gulp.task( 'sassFrontendSource', function() {
	gulp.src( sassFrontendSource )
		.pipe(sass({
			outputStyle: 'compressed'
		})
			.on( 'error', gutil.log ) )
		.pipe(gulp.dest( 'assets/css' ) )
});

// Task to compile Backend SASS file
gulp.task( 'sassBackendSource', function() {
	gulp.src( sassBackendSource )
		.pipe(sass({
			outputStyle: 'compressed'
		})
			.on( 'error', gutil.log) )
		.pipe(gulp.dest( 'assets/css' ) );
});

// Task to concatenate and uglify js files
gulp.task( 'concatAdminJs', function() {
	gulp.src(jsBackendSource ) // use jsSources
		.pipe(concat( 'wpbooklist_search_admin.min.js' ) ) // Concat to a file named 'script.js'
		.pipe(uglify() ) // Uglify concatenated file
		.pipe(gulp.dest( 'assets/js' ) ); // The destination for the concatenated and uglified file
});

// Task to concatenate and uglify js files
gulp.task( 'concatFrontendJs', function() {
	gulp.src(jsFrontendSource ) // use jsSources
		.pipe(concat( 'wpbooklist_search_frontend.min.js' ) ) // Concat to a file named 'script.js'
		.pipe(uglify() ) // Uglify concatenated file
		.pipe(gulp.dest( 'assets/js' ) ); // The destination for the concatenated and uglified file
});

gulp.task( 'copyassets', function () {
	gulp.src([ './assets/**/*' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-search_dist' ) );
});

gulp.task( 'copyincludes', function () {
	gulp.src([ './includes/**/*' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-search_dist' ) );
});

gulp.task( 'copyconfig', function () {
	gulp.src([ './wpbooklistconfig.ini' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-search_dist' ) );
});

gulp.task( 'copyreadme', function () {
	gulp.src([ './readme.txt' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-search_dist' ) );
});

gulp.task( 'copylang', function () {
	gulp.src([ './languages/**/*' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-search_dist' ) );
});

gulp.task( 'copymainfile', function () {
	gulp.src([ './wpbooklist-search.php' ], {base: './'}).pipe(gulp.dest( '../wpbooklist-search_dist' ) );
});

gulp.task('copyuifile', function () {
    gulp.src(['./class-admin-books-search-tab-extension-ui.php'], {base: './'}).pipe(gulp.dest('../wpbooklist-search_dist'));
});

gulp.task( 'zip', function () {
	return gulp.src( '../wpbooklist-search_dist/**' )
		.pipe(zip( 'wpbooklist-search.zip' ) )
		.pipe(gulp.dest( '../wpbooklist-search_dist' ) );
});

gulp.task( 'cleanzip', function(cb) {
	del([ '../wpbooklist-search_dist/**/*' ], {force: true}, cb);
});

gulp.task( 'clean', function(cb) {
	del([ '../wpbooklist-search_dist/**/*', '!../wpbooklist-search_dist/wpbooklist-search.zip' ], {force: true}, cb);
});

// Task to watch for changes in our file sources
gulp.task( 'watch', function() {
	gulp.watch(sassWatch,[ 'sassFrontendSource', 'sassBackendSource' ]);
	gulp.watch(jsFrontendWatch,[ 'concatFrontendJs' ]);
	gulp.watch(jsBackendWatch,[ 'concatAdminJs' ]);
});

// Default gulp task
gulp.task( 'default', [ 'sassFrontendSource', 'sassBackendSource', 'concatAdminJs', 'concatFrontendJs', 'watch' ]);


//gulp.task( 'default', [ 'cleanzip' ]);

//gulp.task( 'default', [ 'copyassets', 'copyincludes', 'copyreadme', 'copylang', 'copymainfile' , 'copyuifile' ]);

//gulp.task( 'default', [ 'zip' ]);

//gulp.task( 'default', [ 'clean' ]);