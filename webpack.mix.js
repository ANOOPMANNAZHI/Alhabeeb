const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js');
  // .js('resources/js/jquery.min.js', 'public/js')
 //  .js('resources/js/plms-app.js', 'public/js')
   //.js('resources/js/layout.js', 'public/js')
//   .js('resources/js/theme-color.js', 'public/js')
 //  .js('resources/js/scripts.js', 'public/js')
  // .js('resources/js/jquery.blockui.min.js', 'public/js')
  // .js('resources/js/jquery.slimscroll.min.js', 'public/js')
 //  .js('resources/js/jquery.sparkline.min.js', 'public/js')
  // .js('resources/js/sparkline-data.js', 'public/js')
 //  .js('resources/js/material.min.js', 'public/js')
  // .js('resources/js/bootstrap.js', 'public/js')
   
//   .sass('resources/sass/app.scss', 'public/css')
   // .styles([
   //  'public/plugins/bootstrap/css/bootstrap.min.css',
   //  'public/plugins/summernote/summernote.css',
   //  'public/plugins/simple-line-icons/simple-line-icons.min.css',
   //  'public/plugins/font-awesome/css/font-awesome.min.css',
   //  'public/css/extra_pages.css',
   //  'public/plugins/iconic/css/material-design-iconic-font.min.css',
   //  'public/plugins/material/material.min.css',
   //  'public/css/material_style.css',
   //  'public/css/animate_page.css',
   //  'public/css/custom.css',
   //  'public/css/plugins.min.css',
   //  'public/css/responsive.css',
   //  'public/css/theme-color.css',
   //  'public/css/theme-color.css',
   // ], 'public/css/all.css');


   mix.scripts([
     'public/js/jquery.min.js',
     'public/js/popper.min.js',
     'public/js/jquery.blockui.min.js',
     'public/js/jquery.slimscroll.min.js',
     'public/plugins/bootstrap/js/bootstrap.min.js',
     'public/js/jquery.sparkline.min.js',
     'public/js/sparkline-data.js',
     'public/js/plms-app.js',
     'public/js/layout.js',
     'public/js/theme-color.js',
    // 'public/js/scripts.js',
     'public/plugins/material/material.min.js',
     'public/js/animations.js',
     'public/js/datatables.min.js'
    ], 'public/js/all.js'); 
   
  // .sass('resources/sass/app.scss', 'public/css');
