define(['jquery', 'jqueryui'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {

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
                    $('.toggler-closed').addClass('click-again').click();
//                    $('.click-again').removeClass('click-again').click();
                });
            };

            var toggleAdmins = function() {
                $("#toggle-admins").on('click', function() {
                    if ($(this).hasClass('hide-admins')) {
                        $('.admins-col').hide();
                        $(this).addClass('show-admins');
                        $(this).removeClass('hide-admins');
                        $(this).html('Show Admins');
                    } else {
                        $('.admins-col').show();
                        $(this).addClass('hide-admins');
                        $(this).removeClass('show-admins');
                        $(this).html('Hide Admins');
                    }
                });
            };

            var toggleType = function() {
                $(".toggle-type.toggler-open").on('click', function(event) {
                    if (event.altKey) {
                        $('.toggler-open').click();
                    } else {
                        var type = $(this).attr('type');
                        $("tr." + type).hide();
                        $(this).hide().parent().find('.toggler-closed').show();
                    }
                });
                $(".toggle-type.toggler-closed").on('click', function(event) {
                    if (event.altKey) {
                        $('.toggler-closed').click();
                    } else {
                        var type = $(this).attr('type');
                        $("tr." + type).show();
                        if ($('#toggle-core').hasClass('show-core')) {
                            $("tr." + type + ".std").hide();
                        }
                        $(this).hide().parent().find('.toggler-open').show();
                    }
                });
            };

            var importExcel = function() {
                $("#import-excel-btn").on('click', function() {
                    console.log('Now importing Excel data - please be patient...');
                    $('#import-excel').show();
                    $('.plugins').hide();
                });
            };

            var cancelImport = function() {
                $('#cancel-import-btn').on('click', function() {
                    console.log('cancelling import...!');
                    $('#import-excel').hide();
                    $('.plugins').show();
                });
            };

            /**
             * Initialize all functions
             */
            var initFunctions = function() {
                showDetails();
                closeDetails();
                toggleCore();
                toggleAdmins();
                toggleType();
                importExcel();
                cancelImport();
            };


            /**
             * The document is ready
             */
            $(document).ready(function() {
                console.log('=================< reports_plugins/navigation.js >=================');
                initFunctions();
                $('#import-excel').hide();
                $('#toggle-core').click();
                $('.toggler-open').each(function() {
                   $(this).click();
                });
            });
        }
    };
});
