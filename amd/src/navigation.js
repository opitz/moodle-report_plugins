define(['jquery', 'jqueryui'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {

            var toggleCore = function() {
                $("#toggle-core").on('click', function() {
                    if ($(this).hasClass('hide-core')) {
                        $('.std').hide();
                        $(this).addClass('show-core');
                        $(this).removeClass('hide-core');
                        $(this).html('Show Core');
                    } else {
                        $('.std').show();
                        $(this).addClass('hide-core');
                        $(this).removeClass('show-core');
                        $(this).html('Hide Core');
                    }
                });
            };

                    /**
             * Initialize all functions
             */
            var initFunctions = function() {
                toggleCore();
            };


            /**
             * The document is ready
             */
            $(document).ready(function() {
                console.log('=================< reports_plugins/navigation.js >=================');
                initFunctions();
            });
        }
    };
});
