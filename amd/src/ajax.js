define(['jquery', 'jqueryui'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {
            var getCourseAdmins = function() {
                $('.uses').on('click', function() {
                    var displayname = $(this).parent().find('.displayname').html();
                    var pluginname = $(this).parent().attr('pluginname');
                    var plugintype = $(this).parent().attr('type');
                    console.log('pluginname: ' + pluginname);
                    console.log('plugintype: ' + plugintype);
                    $.ajax({
                        url: "ajax/list_admins.php",
                        type: "POST",
                        data: {
                            'displayname': displayname,
                            'pluginname': pluginname,
                            'plugintype': plugintype,
                            'sesskey': M.cfg.sesskey
                        },
                        success: function(result) {
                            $('#details').html(result);
                            $('.details').show();
                            $('.plugins').hide();
                        }
                    });

                });
            };

            /**
             * Initialize all functions
             */
            var initFunctions = function() {
                getCourseAdmins();
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
