const mix_ = require('laravel-mix');

var _asset = './src/assets/';

mix_.setPublicPath('./dist')
    .styles([
        _asset + 'css/main.css',
    ], 'dist/css/main.min.css');

if (mix_.inProduction()) {
    mix_.version();
} else {
    mix_.sourceMaps();
}
