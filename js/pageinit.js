/* (c) 2010 Joseph Fahey
 * Released under the Gnu Public Licence
 *
 * Sets up the tagbox
 *
 * To be used with (and called after) folksonomie.js.
 *
 * For the Facebook features to work, the FB and fK.fb variables
 * need to already exist.
 *
 */

function update_user_box() {
    var user_box = document.getElementById("user");
    user_box.innerHTML = "<span>"
                  + '<fb:profile-pic uid="loggedinuser" facebook-logo="true">'
                  + '</fb:profile-pic>'
                  + 'Bienvenue, <fb:name uid="loggedinuser" useyou="false"></fb:name>.'
                  + ' Vous êtes enregistré avec votre compte Facebook.'
                  + '</span>';

                  FB.XFBML.Host.parseDomTree();
}



/**
 * Called by the Open Id popup window on Open Id login.
 */
window.handleOpenIDResponse = function (openid_args){
    $.ajax({type: "get",
            url: fK.cf.oIdPath + "oid_popup_end.php",
            data: openid_args,
            cache: true, /* must be true, otherwise openid chokes 
                          on extra param*/
            success: function(xhr, msg) {
                fK.oidLogin = true;
                $(fK.events).trigger("loggedIn");
            },
            error: function () {
                fK.oidLogin = false;
                alert("Échec du login.");
            }});
};


fK.fb.onLogin = function() {
        return 1;
};

  $(document).ready(function()
                    {
                        fK.fb = fK.fb || {};

                        // setup according to login state
                        fK.cf.container = $("#folksocontrol");
                        
                        // temporary (Open Id disabled)
                        $(".fKLoginButton", fK.cf.container).hide();
                        $("#fbstuff").hide();
                        $("#connectMe", fK.cf.container).click( function(ev)
                            {
                                ev.preventDefault();
                                $("#login-tabs").show();

                                /* Insert Open Id provider list */
                                var $oidTab = $("#tabs-1");
                                if ($("ul", $oidTab).length === 0) {
//                                    console.log("No ul in tabs-1");
                                    $oidTab.append(fK.oid.providerList());
                                }
                                
                                $("div.explainMessage", 
                                  $("#bloc_folkso")).hide();

                            });

                        /*
                         * Setup login tabs (cloud only)
                         */
                        $("#login-tabs").tabs();

                        $(fK.events).bind('loggedIn',
                                       function() {
                                           console.log("loggedIn firing");
                                           $("#fbkillbox", fK.cf.container).hide();
                                           $(".fKTagbutton").show();
                                           $(".fKTaginput", fK.cf.container).show();
                                           $(".fKLoginButton", fK.cf.container).hide();
                                           $("fb:login-button").hide();
                                           $("#login-tabs").hide();
                                           $("#logout, #logout2").show();
                                           $("ul.provider_list").hide();
                                           $(".firstLogin", fK.cf.container).hide();
                                           $("#folkso-nav").show();
                                           fK.loginStatus = true;
                                           if (jQuery.isFunction(fK.tinymceInit)) {
                                               fK.tinymceInit();
                                           }
                                       });


                        $(fK.events).bind('loggedOut',
                                       function() { 
                                           fK.loginStatus = false;
                                           if ($.cookie('folksofblogin')) {
                                               $.cookie("folksofblogin",
                                                        null,
                                                        {domain: ".fabula.org",
                                                         path: "/"});
                                           }
                                       });

                        /* Sets up event handler: $(fK.events).bind("loggedIn") */
                        fK.fn.pollFolksoCookie();

                        /* Facebook connect login and logout events */
                        fK.fb.loggedUser = function()
                        {
                            fK.fn.completeFBlogin(
                                function()
                                { 
                                    if (fK.loginStatus === false) {
                                        $(fK.events).trigger('loggedIn');
                                        fK.loginStatus = true; 
                                        fK.fbLogin = true;
                                        $.cookie("folksofblogin", "fb",
                                                 {domain: ".fabula.org",
                                                  path: "/",
                                                  expires: 14});
                                    }
                                    if ($.isFunction(fK.ufn.loggedIn)) {
                                        fK.ufn.loggedIn();
                                    }
                                },
                                /* nothing here, because we might be logged in by 
                                 * other means (session valid but FB unlogged, 
                                 * logged via OpenId etc. Failuer to log via FB should 
                                 * probably not have any effect on anything.*/
                                function() { }
                            );
                        };

                        fK.fb.unLoggedUser = function()
                        {
                            fK.fbLogin = false;
                            // Do nothing. See above.
                            // $('body').trigger('loggedOut');
                        };

                        /*
                         * Initialize FB. Default is to use functions
                         *  for reacting to login state. If
                         *  fK.cf.facebookReload is set, we reload instead.
                         */
                        if (FB && fK.fb.sitevars) {
                            FB.init(fK.fb.sitevars.apikey,
                                    fK.fb.sitevars.xdm,
                                    fK.cf.facebookReload ? 
                                    {"reloadIfSessionStateChanged" : true } 
                                    : {"ifUserConnected": fK.fb.loggedUser,
                                       "ifUserNotConnected": fK.fb.unLoggedUser}
                                   );
                        }

                        var hostAndPath = 'http://www.fabula.org/tags/';
                        fK.init({
                                  autocompleteUrl: hostAndPath + 'tagcomplete.php',
                                  postResUrl: hostAndPath + 'resource.php',
                                  getResUrl: hostAndPath + 'resource.php',
                                  getUserUrl: hostAndPath + 'user.php',
                                  postTagUrl: hostAndPath + 'tag.php',
                                  oIdLogoPath: "/tags/logos/",
                                  oIdPath: '/tags/fdent/'
                            });

                        function setupLogin () {
                            return function (ev) {
                                ev.preventDefault();
                                $(this).parent().append( fK.oid.providerList() );
                            };
                        }

                        /*
                         * Hide all the login only stuff
                         */
                        function notLogged () {
                            $(".login-only").hide();
                            $("#logout").hide();
                            $(".logout-link").hide();
                            $(".fKTagbutton", fK.cf.container).hide();
                            $("input.fKTaginput").hide();
                            $(".fKLoginButton").click(setupLogin());
//                            $("#login-tabs").hide();
                            $("a.firstLogin").show();
                            $("#folkso-nav").hide();
                        }

                        if (fK.loginStatus) {
                            $(fK.events).trigger('loggedIn');
                        }
                        else {
                            notLogged();
                        }

                        $("input.fKTaginput", fK.cf.container)
                            .autocomplete({source: fK.cf.autocompleteUrl,
                                          minLength: 2});
                        $("input.fKTaginput", $("#newsubscriptions"))
                            .autocomplete({source: fK.cf.autocompleteUrl,
                                          minLength: 2});


                        if ($("#bloc_orange ul").length == 0) {
                            $("#bloc_orange").hide();
                        }

                        function tagAddTarget() {
                            if ($("#bloc_orange ul").length > 0) {
                                return $("#bloc_orange ul");
                            }
                            else {
                                $("#bloc_orange")
                                    .append($("<div class='tagcloud'>" +
                                              "<ul class='cloudlist'></ul>" +
                                              "</div>"));
                                return $("#bloc_orange ul");
                            }
                        }

                        $(".fKTagbutton").click(
                            fK.fn.tagres_react($("input.fKTaginput"),
                                               tagAddTarget()));

                        $(".fKTagbutton").click(
                            function() {
                                $("#bloc_orange").show();
                        });

                        $("#showWhatIs").click(
                            function(ev) {
                                ev.preventDefault();
                                $("div.explainMessage").show();
                                $(this).css({fontWeight: "bold", 
                                             fontStyle: "italic"});
                            });

                        $("#closeExplain").click(
                            function(ev) {
                                ev.preventDefault();
                                var $here = $(this);
                                $here.parent("div.explainMessage").hide();
                                $("#showWhatIs").css({fontWeight: "normal", 
                                                      fontStyle: "normal"});
                            }
                        );

                        $("#logout, #logout2")
                            .click(
                                function(ev){
                                    ev.preventDefault();
                                    if ($.cookie('folksofblogin') == "fb") {
                                        FB.Integration.logout(
                                            function()
                                            {
                                                $.cookie("folksosess", null,
                                                         {domain: ".fabula.org",
                                                          path: "/"});
                                                $(fK.events).trigger("loggedOut");
                                                notLogged();   
                                            });
                                    }
                                    else {
                                        $.cookie("folksosess", null,
                                                 {domain: ".fabula.org",
                                                  path: "/"});
                                        $(fK.events).trigger("loggedOut");
                                        notLogged();                 
                                    }
                                });
                    });


