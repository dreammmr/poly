var gulp = require('gulp'),
    sass = require('gulp-ruby-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    cssnano = require('gulp-cssnano'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    livereload = require('gulp-livereload'),
    del = require('del');

gulp.task('styles', function() {
  return sass('assets/scss/main.scss', { style: 'expanded' })
    .pipe(gulp.dest('assets/dist/css'))
    .pipe(rename({suffix: '.min'}))
    .pipe(cssnano())
    .pipe(gulp.dest('assets/dist/css'))
    .pipe(notify({ message: 'SCSS files done motherfucker!' }));
});

gulp.task('scripts', function() {
  return gulp.src('assets/js/**/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter('default'))
    .pipe(concat('main.js'))
    .pipe(gulp.dest('assets/dist/js'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest('assets/dist/js'))
    .pipe(notify({ message: 'JS files done!' }));
});

gulp.task('clean', function() {
  return del(['assets/dist/css', 'assets/dist/js']);
});

gulp.task('default', ['clean'], function() {
  gulp.start('styles', 'scripts');
});

gulp.task('watch', function() {

  gulp.watch('assets/scss/**/*.scss', ['styles']);

  gulp.watch('assets/js/**/*.js', ['scripts']);

});