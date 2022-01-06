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
                $(".toggle-type.toggler-open").on('click', function() {
                    var type = $(this).attr('type');
                    $("tr." + type).hide();
                    $(this).hide().parent().find('.toggler-closed').show();
                });
                $(".toggle-type.toggler-closed").on('click', function() {
                    var type = $(this).attr('type');
                    $("tr." + type).show();
                    if ($('#toggle-core').hasClass('show-core')) {
                        $("tr." + type + ".std").hide();
                    }
                    $(this).hide().parent().find('.toggler-open').show();
                });
            };

            /**
             * Initialize all functions
             */
            var initFunctions = function() {
                toggleCore();
                toggleAdmins();
                toggleType();
            };


            /**
             * The document is ready
             */
            $(document).ready(function() {
                console.log('=================< reports_plugins/navigation.js >=================');
                initFunctions();
                $('#toggle-core').click();
                $('.toggler-open').each(function() {
                   $(this).click();
                });
            });
        }
    };
});
