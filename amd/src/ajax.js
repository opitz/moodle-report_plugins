define(['jquery', 'jqueryui'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {
            var import_excel = function() {
                $("#import-excel").on('click', function() {
                    console.log('importing excel data here...');
                });
            };

            var showDetails = function() {
                $('.displayname').on('click', function() {
                    var displayName = $(this).html();
                    var installPath = $(this).parent().find('.rootdir').html();
                    var version = $(this).parent().find('.versiondb').html();
                    var release = $(this).parent().find('.release').html();
                    var uses = $(this).parent().find('.uses').html();
                    var frankenstyle = $(this).parent().find('.frankenstyle').html();
                    console.log('==> clicking displayname ' + displayName);

                    $.ajax({
                        url: "ajax/plugin_details.php",
                        type: "POST",
                        data: {
                            'displayname': displayName,
                            'installpath': installPath,
                            'version': version,
                            'release': release,
                            'uses': uses,
                            'frankenstyle': frankenstyle,
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

            var closeDetails = function() {
                $('#close-details').on('click', function() {
                   $('.details').hide();
                   $('.plugins').show();
                });
            };

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
                showDetails();
                closeDetails();
                getCourseAdmins();
                import_excel();
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
