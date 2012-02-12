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


$(document).ready(function()
                    {
                        // setup according to login state
                        fK.cf.container = $("#folksocontrol");

                        // Fabula specific:
                        if ((typeof Nifty !== "undefined") && 
                            $.isFunction(Nifty)) {
                            Nifty("div#bloc_folkso");
                        }

                        /* CLAG(if ($("#folksocontrol").length  == 0) 
                          {console.error("folksocontrol div not found"); } )*/
                        
                        // temporary (Open Id disabled)
                        $(fK.events).bind('loggedIn',
                                       function() {
                                           /* CLAG(console.log("pageinit: loggedIn triggered");) */

                                           $("#fbkillbox", fK.cf.container).hide();
                                           $(".fKTagbutton").show();
                                           $(".fKTaginput", fK.cf.container).show();
                                           $(".fKLoginButton", fK.cf.container).hide();
                                           $("#logout, #logout2").show();
  //                                          $("ul.provider_list").hide();
                                           $(".firstLogin", fK.cf.container).hide();
                                           $("#folkso-nav").show();
                                           fK.loginStatus = true;
                                       });


                        $(fK.events).bind('loggedOut',
                                       function() { 
                                           /* CLAG(console.log("pageinit: loggedOut triggered");) */
                                           fK.loginStatus = false;
                                           $(".fKTaginput", fK.cf.container).hide();
                                           $(".fKLoginButton", fK.cf.container).show();
                                           $("#logout, #logout2").hide();
//                                           $("ul.provider_list", fK.cf.container).hide();
                                           $("#folkso-nav").hide();
                                       });

                        /*
                         * Event triggered only on actual logout, ie. not on page load. Just when 
                         * a button gets pushed.
                         */
                        $(fK.events).bind('userLogout',
                                          function() {
                                              // in folkso-user we eliminate tinyMCE here
                                          });

                        /* Sets up event handler: $(fK.events).bind("loggedIn") */
//                        fK.fn.pollFolksoCookie();

                        var hostAndPath = '/tags/';
                        fK.init({
                                  autocompleteUrl: '/tags/tagcomplete.php',
                                  postResUrl: hostAndPath + 'resource.php',
                                  getResUrl: hostAndPath + 'resource.php',
                                  getUserUrl: hostAndPath + 'user.php',
                                  postTagUrl: hostAndPath + 'tag.php',
                                  oIdLogoPath: "/tags/logos/",
                                  oIdPath: '/tags/fdent/'
                            });

                        /*
                         * Hide all the login only stuff
                         */
                        function notLogged () {
                            $(".login-only").hide();
                            $("#logout").hide();
                            $(".logout-link").hide();
                            $(".fKTagbutton", fK.cf.container).hide();
//                            $("input.fKTaginput").hide();
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

                        $("input.fKTaginput")
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

                    });


