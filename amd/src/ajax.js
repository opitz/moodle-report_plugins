define(['jquery', 'jqueryui'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {

            /**
             * Initialize all functions
             */
            var initFunctions = function() {
            };


            /**
             * The document is ready
             */
            $(document).ready(function() {
                console.log('=================< reports_plugins/ajax.js >=================');
                initFunctions();
            });
        }
    };
});
