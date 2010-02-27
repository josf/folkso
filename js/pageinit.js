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
                        fK.fb = fK.fb || {};

                        $('body').bind('loggedIn',
                                       function() {
                                         $("#fbkillbox", fK.cf.container).hide();
                                         $(".fKTagbutton").show();
                                         $(".fKTaginput", fK.cf.container).show();
                                         $(".fKLoginButton", fK.cf.container).hide();
                                         $("fb:login-button").hide();
                                         $("ul.provider_list").hide();
                                       });


                        /* Sets up event handler: $("body").bind("loggedIn") */
                        fK.fn.pollFolksoCookie();

                        fK.fb.loggedUser = function() 
                        {
                            $('body').trigger('loggedIn');
                        };

                        if (FB && fK.fb.sitevars) {
                            FB.init(fK.fb.sitevars.apikey,
                                    fK.fb.sitevars.xdm,
                                    {"ifUserConnected": fK.fb.loggedUser, 
                                     "ifUserNotConnected": fK.fb.unLoggedUser}
                                   );
                        }
                      var hostAndPath = 'http://www.fabula.org/tags/', tagAddTarget;
                      fK.init({
                                  autocompleteUrl: hostAndPath + 'tagcomplete.php',
                                  postResUrl: hostAndPath + 'resource.php',
                                  getResUrl: hostAndPath + 'resource.php',
                                  oIdLogoPath: "/tags/logos/",
                                  oIdPath: '/tags/fdent/'
                            });

                      function setupLogin () {
                        return function (ev) {
                          ev.preventDefault();
                          $(this).parent().append( fK.oid.providerList() );
                        };
                      }

                      window.handleOpenIDResponse = function (openid_args){
                        $("#bucket").html("Verifying OpenID response");
                        $.ajax({type: "get",
                              url: fK.cf.oIdPath + "oid_popup_end.php",
                              data: openid_args,
                              success: function(msg) {
                              $("#bucket").html(msg);
                            }});
                      };

                      // setup according to login state
                      fK.cf.container = $("#folksocontrol");


                          
                      if (fK.loginStatus) {
                        $("#fbkillbox", fK.cf.container).hide();
                        $(".fKLoginButton", fK.cf.container).hide();
                      }
                      else {
                        $(".fKTagbutton", fK.cf.container).hide();
                        $("input.fKTaginput").hide();
                        $(".fKLoginButton").click(setupLogin());



                      }

                        $("input.fKTaginput", fK.cf.container).autocomplete(fK.cf.autocompleteUrl);

                        function tagAddTarget() {
                            if ($("#bloc_orange ul").length > 0) {
                                return $("#bloc_orange ul");
                            }
                            else {
                                var newbox = $('<div id="bloc_orange">'
                                           + '<b class="niftycorners">'
                                           + '<h3>Mots clés : </h3>'
                                           + '<div class="tagcloud">'
                                           + '<ul class="cloudlist">'
                                           + '</ul></div></div>');
//                                fK.fn.buildCloud($("ul", newbox));
                                $("#bloc_folkso").after(newbox);
                                return $("ul", newbox);
                            }
                        }

                      $(".fKTagbutton").click(fK.fn.tagres_react($("input.fKTaginput"),
                                                                 tagAddTarget()));
                    });


