/* (c) 2010 Joseph Fahey
 * Released under the Gnu Public Licence
 * 
 * Sets up the tagbox 
 * 
 * To be used with (and called after) folksonomie.js.
 * 
 */

  $(document).ready(function()
                    {
                      var hostAndPath = 'http://www.fabula.org/tags/';
                      fK.init({
                        autocompleteUrl: hostAndPath + 'tagcomplete.php',
                            postResUrl: hostAndPath + 'resource.php'
                            });

                      fK.oid.logopath = "/tags/logos/";
                      fK.oid.oidpath = "/tags/fdent/";

                      function setupLogin () {
                        return function (ev) {
                          ev.preventDefault();
                          $(this).parent().append( fK.oid.providerList() );
                        };
                      }

                      window.handleOpenIDResponse = function (openid_args){
                        $("#bucket").html("Verifying OpenID response");
                        $.ajax({type: "get",
                              url: fK.oid.oidpath + "oid_popup_end.php",
                              data: openid_args,
                              success: function(msg) {
                              $("#bucket").html(msg);
                            }});
                      };

                      // setup according to login state
                      fK.cf.container = $("#folksocontrol");

                        $('body').bind('loggedIn',
                                       function() {
                                         $("#fbkillbox", fK.cf.container).hide();
                                         $(".fKTagbutton").show();
                                         $(".fKTaginput", fK.cf.container).show();
                                         $(".fKLoginButton", fK.cf.container).hide();
                                         $("fb:login-button").hide();
                                         $("ul.provider_list").hide();
                                       });

                          
                      if (fK.loginStatus) {
                        $("#fbkillbox", fK.cf.container).hide();
                        $(".fKLoginButton", fK.cf.container).hide();
                      }
                      else {
                        $(".fKTagbutton", fK.cf.container).hide();
                        $("input.fKTaginput").hide();
                        $(".fKLoginButton").click(setupLogin());

                        /* Sets up event handler: $("body").bind("loggedIn") */
                        fK.fn.pollFolksoCookie();

                      }
                      $("input.fKTaginput", fK.cf.container).autocomplete(fK.cf.autocompleteUrl);
                      $(".fKTagbutton").click(fK.fn.tagres_react($("input.fKTaginput"),
                                                                  $("ul.tagcloud")));
                    });

