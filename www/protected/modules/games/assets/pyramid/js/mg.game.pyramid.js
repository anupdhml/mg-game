var device_ratio = 1,
    is_touch_device = is_touch_device();

if (is_touch_device) {
    device_ratio = window.devicePixelRatio;
}

MG_GAME_PYRAMID = function ($) {
    return $.extend(MG_GAME_API, {
        wordField:null,
        playOnceMoveOnFinalScreenWaitingTime:15000, // milliseconds
        submitButton:null,
        image:null,
        licence_info:[],
        more_info:null,
        levels:[],
        level:1,
        level_step:3,
        words:[],
        /*
         * initialize the game. called from inline script generated by the view
         */
        init:function (options) {
            if (is_touch_device) {
                $("body").addClass('touch_device');
            }
//            alert(device_ratio);

            BrowserDetect.init();
            $("body").addClass(BrowserDetect.browser);
            $(window).resize(function() {
                onResize ();
            });

//            alert("height" + $(window).height());
//            alert("width" + $(window).width());

            $('#countdown').countdown({until:'+2m', layout: '{mnn}{sep}{snn}', onExpiry:MG_GAME_PYRAMID.liftOff});
            var settings = $.extend(options, {
                ongameinit:MG_GAME_PYRAMID.ongameinit
            });
            $("#fieldholder").hide();

            $("#container").css("height", $(window).height() - 200);

            MG_GAME_PYRAMID.wordField = $("#word");

            // submit on enter
            MG_GAME_PYRAMID.wordField.focus().keydown(function (event) {
                if (event.keyCode == 13) {
                    MG_GAME_PYRAMID.onsubmit();
                    return false;
                }
            });

            MG_GAME_PYRAMID.submitButton = $("#button-play").click(MG_GAME_PYRAMID.onsubmit);
            // Delete the default footer content.
            $("#footer").html("");
            MG_GAME_API.game_init(settings);

            $("#container").find("footer div").html("4 Letters!");
        },

        /*
         * display games turn
         */
        renderTurn:function (response) {
            $("#stage").hide();

            $("#image_container").html("");

            var turn_info = {
                url:response.turn.images[0].full_size,
                url_full_size:response.turn.images[0].full_size,
                licence_info:MG_GAME_API.parseLicenceInfo(response.turn.licences)
            };

            $("#template-turn").tmpl(turn_info).appendTo($("#image_container")).after(function () {
                onResize ();
            });

            $("#licences").html("");
            $("#template-licence").tmpl(MG_GAME_PYRAMID.licence_info).appendTo($("#licences"));

            $("#more_info").html("");

            if (MG_GAME_PYRAMID.more_info != null &&
                MG_GAME_PYRAMID.more_info.hasOwnProperty("url"))
                $("#template-more-info").tmpl(MG_GAME_PYRAMID.more_info).appendTo($("#more_info"));

            $("a[rel='zoom']").fancybox({overlayColor:'#000'});

            $("#stage").fadeIn(1000, function () {
                MG_GAME_PYRAMID.busy = false;
                MG_GAME_PYRAMID.wordField.focus();
            });
        },

        /*
         * display the final turn
         */
        renderFinal:function () {
            $("#stage").hide();

            $('#game_description').hide();

            $("#fieldholder").hide().html("");

            $("#input_area").html("");
            $("#input_area").hide();

            var final_info = {
                finalMsg:"You got to " + (MG_GAME_PYRAMID.level+MG_GAME_PYRAMID.level_step-1) + " letters! Amazing!"
            }

            $("#template-final-info").tmpl(final_info).appendTo($("#fieldholder"));
            $("#fieldholder").show();
            $("#gamearea").hide();
            $("#content").find("header").hide();
            $("#content").find("footer").hide();

            var level_info = {
                tag:"",
                width:0
            }

            var fix = $("#fieldholder");
            for (var i in MG_GAME_PYRAMID.levels) {
                var level = MG_GAME_PYRAMID.levels[i];
                fix.find(".level_" + level.level).html(level.tag.tag);
                fix.find(".level_" + level.level).removeClass("level_" + level.level).addClass("word_level_" + level.level);
            }

            $("#licences").html("");
            $("#template-licence").tmpl(MG_GAME_PYRAMID.licence_info).appendTo($("#licences"));

            $("#more_info").html("");

            if (MG_GAME_PYRAMID.more_info != null && MG_GAME_PYRAMID.more_info.hasOwnProperty("url"))
                $("#template-more-info").tmpl(MG_GAME_PYRAMID.more_info).appendTo($("#more_info"));


            $("#image_container").html("");
            if (MG_GAME_PYRAMID.game.play_once_and_move_on == 1) {
                var info = {
                    remainingTime:null,
                    play_once_and_move_on_url:null
                };
                info.remainingTime = (MG_GAME_NEXTAG.playOnceMoveOnFinalScreenWaitingTime / 1000);
                info.play_once_and_move_on_url = MG_GAME_NEXTAG.game.play_once_and_move_on_url;

                $("#template-final-info-play-once").tmpl(info).appendTo($("#fieldholder"));
                $("#template-final-summary-play-once").tmpl(info).appendTo($("#image_container"));
                $("#box1").hide();
                window.setTimeout(function () {
                    window.location = info.play_once_and_move_on_url;
                }, MG_GAME_PYRAMID.playOnceMoveOnFinalScreenWaitingTime);

                var updateRemainingTime = function () {
                    MG_GAME_PYRAMID.playOnceMoveOnFinalScreenWaitingTime -= 1000;
                    if (MG_GAME_PYRAMID.playOnceMoveOnFinalScreenWaitingTime >= 1) {
                        $('#remainingTime').text(MG_GAME_PYRAMID.playOnceMoveOnFinalScreenWaitingTime / 1000);
                        window.setTimeout(updateRemainingTime, 1000);
                    }
                }
                window.setTimeout(updateRemainingTime, 1000);
            }

            $("#image_review img").height(($(window).height() - 30 - 89 - 100 - 40) / 2);

            onResize ();

            $("a[rel='zoom']").fancybox({overlayColor:'#000'});

            MG_GAME_API.releaseOnBeforeUnload();

            $("#button-play-again").click(function (event) {
                event.preventDefault();
                //TODO fix sound
                //$('#next_level')[0].play();
                location.reload();
            });

            $("#stage").fadeIn(1000, function () {
                MG_GAME_PYRAMID.busy = false;
                MG_GAME_PYRAMID.wordField.focus();
            });
        },

        /*
         * evaluate each response from /api/games/play calls (POST or GET)
         */
        onresponse:function (response) {
            MG_GAME_API.curtain.hide();

            if ($.trim(MG_GAME_PYRAMID.game.more_info_url) != "")
                MG_GAME_PYRAMID.more_info = {url:MG_GAME_PYRAMID.game.more_info_url, name:MG_GAME_PYRAMID.game.name};

            MG_GAME_PYRAMID.image = response.turn.images[0]

            var accepted = {
                level:1,
                tag:""
            };

            var turn = response.turn;
            for (i_img in turn.tags.user) {
                var image = turn.tags.user[i_img];
                for (i_tag in image) {
                    // PASSING: If we find the passing tag, we just skip it.
                    if (i_tag == MG_GAME_PYRAMID.passStringFiltered) {
                        continue;
                    }
                    var tag = image[i_tag];

                    if (turn.images[0].tag_accepted) {
                        accepted.level = turn.images[0].level;
                        accepted.tag = tag;
                        MG_GAME_PYRAMID.levels.push(accepted);
                        MG_GAME_PYRAMID.nextlevel();
                    } else {
                        $().toastmessage("showToast", {
                            text:"No match! Try again",
                            position:"middle-center",
                            type:"notice"
                        });
                        $('#try_again')[0].play();
                    }
                }
            }

            if (turn.licences.length) {
                for (licence in turn.licences) { // licences
                    var found = false;
                    for (l_index in MG_GAME_PYRAMID.licence_info) {
                        if (MG_GAME_PYRAMID.licence_info[l_index].id == turn.licences[licence].id) {
                            found = true;
                            break;
                        }
                    }

                    if (!found)
                        MG_GAME_PYRAMID.licence_info.push(turn.licences[licence]);
                }
            }
            MG_GAME_API.renderTurn(response);
        },


        /*
         * on callback for the submit button
         */
        onsubmit:function () {
            if (!MG_GAME_PYRAMID.busy) {
                var tags = $.trim(MG_GAME_PYRAMID.wordField.val());
                if (tags == "") {
                    $().toastmessage("showToast", {
                        text:"<h1>Ooops</h1><p>Please enter at least one word</p>",
                        position:"middle-center",
                        type:"notice"
                    });
                    $('#try_again')[0].play();
                } else if (tags.length != (MG_GAME_PYRAMID.level + MG_GAME_PYRAMID.level_step)) {
                    $().toastmessage("showToast", {
                        text:"That wasn't a " + (MG_GAME_PYRAMID.level + MG_GAME_PYRAMID.level_step) + " letters word!",
                        position:"middle-center",
                        type:"notice"
                    });
                    $('#try_again')[0].play();
                } else if($.inArray(tags,MG_GAME_PYRAMID.words) !== -1){
                    $().toastmessage("showToast", {
                        text:"You already submitted this word!",
                        position:"middle-center",
                        type:"notice"
                    });
                    $('#try_again')[0].play();
                } else {
                    MG_GAME_PYRAMID.words.push(tags);
                    // text entered
                    MG_GAME_API.curtain.show();
                    MG_GAME_PYRAMID.busy = true;

                    // send ajax call as POST request to validate a turn
                    MG_API.ajaxCall('/games/play/gid/' + MG_GAME_API.settings.gid, function (response) {
                        if (MG_API.checkResponse(response)) {
                            MG_GAME_PYRAMID.wordField.val("");
                            MG_GAME_PYRAMID.onresponse(response);
                        }
                        return false;
                    }, {
                        type:'post',
                        data:{ // this is the data needed for the turn
                            turn:1,
                            played_game_id:MG_GAME_PYRAMID.game.played_game_id,
                            'submissions':[
                                {
                                    image_id:MG_GAME_PYRAMID.image.image_id,
                                    tags:tags
                                }
                            ]
                        }
                    });
                }
            }
            return false;
        },

        /*
         * this method appears to be not used
         */
        submit:function () {
            MG_API.ajaxCall('/games/play/gid/' + MG_GAME_API.settings.gid, function (response) {
                if (MG_API.checkResponse(response)) { // we have to check whether the API returned a HTTP Status 200 but still json.status == "error" response
                    MG_GAME_API.game = $.extend(MG_GAME_API.game, response.game);
                    MG_GAME_API.settings.ongameinit(response);
                }
            });
        },

        /*
         * process /api/games/play get request responses
         */
        ongameinit:function (response) {
            MG_GAME_PYRAMID.onresponse(response);
        },

        liftOff:function () {
            MG_GAME_PYRAMID.renderFinal();
        },

        nextlevel:function () {
            MG_GAME_PYRAMID.level++;
            MG_GAME_PYRAMID.wordField.attr("placeholder", "Enter a " + (MG_GAME_PYRAMID.level + MG_GAME_PYRAMID.level_step) + " letters word");
            $("#content").find("footer").removeClass("footer_level_" + MG_GAME_PYRAMID.level -1).addClass("footer_level_" + MG_GAME_PYRAMID.level).find("div").html(MG_GAME_PYRAMID.level + MG_GAME_PYRAMID.level_step + " Letters!");
            //$("#content").find("footer").removeClass("level_" + MG_GAME_PYRAMID.level -1).addClass("level_" + MG_GAME_PYRAMID.level);
            $('#next_level')[0].play();
        }
    });
}(jQuery);


/* For the new side panel */
$('#sidepanel #tab').toggle(function () {
    $(this).attr("class", "tab_open");
    $('#sidepanel').animate({'right':0});
}, function () {
    // Question: Why does '-290' work, and '-300' push the arrow too
    // far right?
    $(this).attr("class", "tab_closed");
    $('#sidepanel').animate({'right':-290});
});

var BrowserDetect = {
    init: function () {
        this.browser = this.searchString(this.dataBrowser) || "Other";
        this.version = this.searchVersion(navigator.userAgent) ||       this.searchVersion(navigator.appVersion) || "Unknown";
    },

    searchString: function (data) {
        for (var i=0 ; i < data.length ; i++) {
            var dataString = data[i].string;
            this.versionSearchString = data[i].subString;

            if (dataString.indexOf(data[i].subString) != -1) {
                return data[i].identity;
            }
        }
    },

    searchVersion: function (dataString) {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) return;
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    },

    dataBrowser:
        [
            { string: navigator.userAgent, subString: "Chrome",  identity: "Chrome" },
            { string: navigator.userAgent, subString: "MSIE",    identity: "IE" },
            { string: navigator.userAgent, subString: "Firefox", identity: "FF" },
            { string: navigator.userAgent, subString: "Safari",  identity: "Safari" },
            { string: navigator.userAgent, subString: "Opera",   identity: "Opera" }
        ]

};

function is_touch_device() {
    return !!('ontouchstart' in window) // works on most browsers
        || !!('onmsgesturechange' in window); // works on ie10
};

$.fn.centerHorizontal = function () {
    this.css("position", "absolute");
    if (this.outerWidth() < $(window).width()) {
        this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
        return this;
    }
};

$.fn.centerVertival = function () {
    this.css("position", "absolute");
    if (this.outerWidth() < $(window).width()) {
        this.css("top", (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px");
        return this;
    }
};

function onResize () {
    var max_height,
        gamearea = $("#gamearea");

    //$("#content header div").css("left", 0);
    $("#input_area input").css("width", $(window).width()-150 );
    //$("#input_area input").css('cssText', "width: " + $(window).width()-150 + "px !important, border: 1px solid pink !important" );
    $("#content").css("min-height", device_ratio*($(window).height() + 20 - ($("#header").height() + $("#content footer").height())));

    $("#container").css("height", device_ratio*($(window).height() - 200));
    if (is_touch_device) {
        max_height = $(window).height() - $("#content header").height() - $("#content footer").height() - parseInt(gamearea.css('padding-top'), 10) - parseInt(gamearea.css('padding-bottom'), 10);
        $("#image_to_tag").css({'max-height': max_height, 'max-width': $(window).width() - 20});
        $("#gamearea").css("height", max_height);
    } else {
        max_height = $(window).height() - $("#header").height() - $("#content header").height() - $("#content footer").height() - parseInt(gamearea.css('padding-top'), 10) - parseInt(gamearea.css('padding-bottom'), 10);
        $("#image_to_tag").css({'max-height': max_height, 'max-width': $(window).width() - 20});
        $("#gamearea").css("height", max_height);
    }

    //$("#content header div").centerHorizontal();
    $("#content header div").css("width", parseInt($("#input_area").css("width"), 10) + parseInt($("#countdown").css("width") +15, 10));
    //alert('aham');
}