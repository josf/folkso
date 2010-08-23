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
                        $(".firstLogin", fK.cf.container).click( function(ev)
                            {
                                ev.preventDefault();
                                $("#fbstuff").show();
                            });
                        

                        /*
                         * This event should only fire when
                         * fK.loginStatus goes from false to true. The idea is to
                         * avoid multiple calls.
                         */
                        $('body').bind('justLoggedIn',
                                       function() {

                                       });


                        $('body').bind('loggedIn',
                                       function() {
                                           $("#fbkillbox", fK.cf.container).hide();
                                           $(".fKTagbutton").show();
                                           $(".fKTaginput", fK.cf.container).show();
                                           $(".fKLoginButton", fK.cf.container).hide();
                                           $("fb:login-button").hide();
                                           $("ul.provider_list").hide();
                                           $("#fbstuff").hide();
                                           $(".firstLogin", fK.cf.container).hide();
                                           if (fK.loginStatus === false) {
                                               $('body').trigger('justLoggedIn');
                                           }
                                           fK.loginStatus = true;
                                       });


                        $('body').bind('loggedOut',
                                       function() { 
                                           fK.loginStatus = false;
                                       });

                        /* Sets up event handler: $("body").bind("loggedIn") */
                        fK.fn.pollFolksoCookie();

                        /* Facebook connect login and logout events */
                        fK.fb.loggedUser = function()
                        {
                            fK.fn.completeFBlogin(
                                function(){ if (fK.loginStatus === false) {
                                                $('body').trigger('loggedIn');
                                                fK.loginStatus = true; 
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
                                    alert("You are logged");
                                },
                               error: function () {
                                   alert("Fail!");
                               }});
                      };


                      if (fK.loginStatus) {
                          $('body').trigger('loggedIn');
                      }
                      else {
                        $(".fKTagbutton", fK.cf.container).hide();
                        $("input.fKTaginput").hide();
                        $(".fKLoginButton").click(setupLogin());
                       }

                        $("input.fKTaginput", fK.cf.container).autocomplete(fK.cf.autocompleteUrl);


                        if ($("#bloc_orange ul").length == 0) {
                            $("#bloc_orange").hide();
                        }

                        function tagAddTarget() {
                            if ($("#bloc_orange ul").length > 0) {
                                return $("#bloc_orange ul");
                            }
                            else {
                                $("#bloc_orange").append($("<div class='tagcloud'><ul class='cloudlist'></ul></div>"));
                                return $("#bloc_orange ul");
                            }
                        }

                      $(".fKTagbutton").click(fK.fn.tagres_react($("input.fKTaginput"),
                                                                 tagAddTarget()));
                        $(".fKTagbutton").click(
                            function() {
                                $("#bloc_orange").show();
                        });


                    });


