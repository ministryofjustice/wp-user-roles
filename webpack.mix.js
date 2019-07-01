const mix_ = require('laravel-mix');

var _asset = './assets/';

mix_.setPublicPath('./dist')
    .js(_asset + 'js/block.js', 'js/main.min.js')
    .styles([
        _asset + 'css/main.css',
    ], 'dist/css/main.min.css');

if (mix_.inProduction()) {
    mix_.version();
} else {
    mix_.sourceMaps();
}
