require.config({
    baseUrl : (( typeof window.JSConst === 'object') ? window.JSConst.absRefPrefix : '/') + 'src/js'
});
require(['config'], function() {
    require(['jquery'], function($) {
        console.log('hello world', $('body'));
    });
});
