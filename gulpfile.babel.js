import chug from 'gulp-chug';
import concat from 'gulp-concat';
import gulp from 'gulp';
import gulpif from 'gulp-if';
// import gulpSass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import uglifycss from 'gulp-uglifycss';
import yargs from 'yargs';

import sass from 'gulp-sass';
import dartSass from 'sass';

sass.compiler = dartSass;

const { argv } = yargs
  .options({
    rootPath: {
      description: '<path> path to web assets directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
    nodeModulesPath: {
      description: '<path> path to node_modules directory',
      type: 'string',
      requiresArg: true,
      required: false,
    },
  });

const env = process.env.GULP_ENV;
const rootPath = argv.rootPath || 'web/assets';
const shopRootPath = `${rootPath}/shop`;

const config = [
  '--rootPath',
  argv.rootPath || '../../../../../../../web/assets',
  '--nodeModulesPath',
  argv.nodeModulesPath || '../../../../../../../node_modules',
];

// Custom SCSS paths
const customPaths = {
  scss: [
    'app/Resources/assets/shop/scss/main.scss',
  ],
  watch: [
    'app/Resources/assets/shop/scss/**/*.scss',
  ],
};

export const buildAdmin = function buildAdmin() {
  return gulp.src('vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config }));
};
buildAdmin.description = 'Build admin assets.';

export const watchAdmin = function watchAdmin() {
  return gulp.src('vendor/sylius/sylius/src/Sylius/Bundle/AdminBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config, tasks: 'watch' }));
};
watchAdmin.description = 'Watch admin asset sources and rebuild on changes.';

export const buildShop = function buildShop() {
  return gulp.src('vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config }));
};
buildShop.description = 'Build shop assets.';

export const watchShop = function watchShop() {
  return gulp.src('vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/gulpfile.babel.js', { read: false })
    .pipe(chug({ args: config, tasks: 'watch' }));
};
watchShop.description = 'Watch shop asset sources and rebuild on changes.';

// Build custom shop SCSS
export const buildCustomShopCss = function buildCustomShopCss() {
  return gulp.src(customPaths.scss)
    .pipe(gulpif(env !== 'prod', sourcemaps.init()))
    .pipe(sass().on('error', sass.logError))
    .pipe(concat('custom.css'))
    .pipe(gulpif(env === 'prod', uglifycss()))
    .pipe(gulpif(env !== 'prod', sourcemaps.write('./')))
    .pipe(gulp.dest(`${shopRootPath}/css`));
};
buildCustomShopCss.description = 'Build custom shop CSS assets.';

// Watch custom SCSS
export const watchCustomShop = function watchCustomShop() {
  gulp.watch(customPaths.watch, buildCustomShopCss);
};
watchCustomShop.description = 'Watch custom shop SCSS and rebuild on changes.';

// Combined build tasks
export const buildShopAll = gulp.series(buildShop, buildCustomShopCss);
buildShopAll.description = 'Build all shop assets including custom.';

export const watchShopAll = gulp.parallel(watchShop, watchCustomShop);
watchShopAll.description = 'Watch all shop assets including custom.';

export const build = gulp.parallel(buildAdmin, buildShopAll);
build.description = 'Build assets.';

export const watch = gulp.parallel(watchAdmin, watchShopAll);
watch.description = 'Watch all assets.';

gulp.task('admin', buildAdmin);
gulp.task('admin-watch', watchAdmin);
gulp.task('shop', buildShopAll);
gulp.task('shop-watch', watchShopAll);
gulp.task('custom-css', buildCustomShopCss);
gulp.task('custom-watch', watchCustomShop);

export default build;