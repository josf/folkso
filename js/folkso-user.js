

/* subscribed tags Kontroler */

$(document).ready(
    function()
    {
        /* CLAG(console.log("document.ready starting: from folkso-user");) */
        var hostAndPath = '/tags/';
        fK.init({
                    autocompleteUrl: hostAndPath + 'tagcomplete.php',
                    postResUrl: hostAndPath + 'resource.php',
                    getResUrl: hostAndPath + 'resource.php',
                    getUserUrl: hostAndPath + 'user.php',
                    oIdLogoPath: "/tags/logos/",
                    oIdPath: '/tags/fdent/'
                });


        /*
         * Autocompletion for tag input
         */
        $("#newsubbox").autocomplete({ source: fK.cf.autocompleteUrl,
                                       minLength: 3});     


        /*
         * Do nothing: basic error function for when ajax calls return no data.
         */
        var err204 = fK.fn.defErrorFn(204, function() { });

        /*
         * Trigger logout event on Unauthorized
         */
        var notLogged = function (xhr, textStatus, e) {
            $(fK.events).trigger('loggedOut');
        };
        notLogged.errorcode = 401;



        /*******************************************************************
         * Recently tagged list
         * 
         * This is defined first because some of the other functions refer 
         * to this on updates.
         *******************************************************************/

        /*
         * Initialize controller
         */
        var R = window.R =  new fK.Ktl("#recently");
        R.addList("resources",
                  {selector: "ul",
                   init: function(sel, $place, data) {
                       $(sel, $place)
                           .append($("<li><a class='reslink' href='" + data.url +
                                     "'>" + data.title + 
                                     "</a>"));
                   },
                   match: function(data) {
                       return function (item, i) {
                           return item.url == data.url;
                       };
                   }
                  });
        var appendResource = R.appendField("resources"),
        dropResourceList = R.restartList("resources");        

        /*
         *  Build initial list or append to existing list.
         */
        var gotResList = function(xml, status, xhr) 
        {
            dropResourceList();
            $("resource", xml)
                    .each(function()
                          {
                              var res = {}, $resob = $(this);
                              res.numid = $("numid", $resob).text();
                              res.url = $("url", $resob).text();
                              res.title = $("title", $resob).text();
                              appendResource(res);
                          });
            $(R).trigger("update");
        };


        var getRecently_aj = 
            fK.fn.userGetObject({folksorecent: 1},
                                gotResList,
                                fK.fn.errorChoose(
                                    fK.fn.defErrorFn(204,
                                                     function () {
                                                         dropResourceList();
                                                     }),
                                    notLogged,
                                    function() {
                                        if (! $.browser.msie) {
                                            alert("Resource list problem");
                                        }
                                    }));
        getRecently_aj.dataType = "xml";



        /*
         *  Current subscription list
         */
        var K = window.K = new fK.Ktl("#subscriptions");
        K.addList("subscribed",
                  {selector: "ul",
                   init: function(sel, $place, data) {
                       $(sel, $place)
                           .append($("<li><a class='taglink' href='" + data.link +
                                     "'>" + data.display +
                                     "</a> <a class='unsub' "
                                     + " href='#'>Supprimer</a></li>"));
                   },
                   match: function(data) {
                       return function (item, i) {
                           return item.link == data.link;
                       };
                   }
                  });

        var appendTag = K.appendField("subscribed"), 
        unsubTag = K.deletefield("subscribed"),
        dropList = K.restartList("subscribed");

        /**
         * "Success" function for retrieval of a list of current
         *  subscribed tags. Replaces current list.
         */
        var gotList = function (xml, status, xhr) 
        {
            dropList();
            $("tag", xml)
                .each(function()
                      {
                          var tag = {}, $tagob = $(this);
                          tag.numid = $("numid", $tagob).text();
                          tag.tagnorm = $("tagnorm", $tagob).text();
                          tag.link = $("link", $tagob).text();
                          tag.display = $("display", $tagob).text();
                          appendTag(tag);
                      });
            $(K).trigger("update");
        };

        /**
         * "success" function for appending to list of current subscribed
         * tags. 
         */
        var addListItem = function (xml, status, xhr) 
        {
            $("tag", xml)
                .each(function()
                      {
                          var tag = {}, $tagob = $(this);
                          tag.numid = $("numid", $tagob).text();
                          tag.tagnorm = $("tagnorm", $tagob).text();
                          tag.link = $("link", $tagob).text();
                          tag.display = $("display", $tagob).text();
                          appendTag(tag);
                      });
            $(K).trigger("update");
        };
        

        /**
         * "Success" function for tag removal.
         */
        var rmSub = function(xml, status, xhr) 
        {
/*            if (window.console) {
                console.log("I got removed");
            }*/
            var tag = {};
            tag.numid = $("numid", xml).text();
            tag.tagnormm = $("tagnorm", xml).text();
            tag.link = $("link", xml).text();
            tag.display = $("display", xml).text();
            unsubTag(tag);
            $(K).trigger("update");
            $.ajax(getRecently_aj);
        };


        var getList_aj = 
            fK.fn.userGetObject({folksosubscribed: 1},
                                gotList,
                                fK.fn.errorChoose(err204, notLogged,
                                                  function(xhr, textStatus, e) { 
                                                      if (! $.browser.msie) {
                                                          alert("list getting failed"); 
                                                      }
                                                  })
                               );

        // tag needs to be defined before using this
        var addSub_aj = fK.fn.userPostObject({folksoaddsubscription: 1,
                                              folksotag: undefined},
                                             addListItem,
                                             fK.fn.errorChoose(
                                                 notLogged,
                                                 function(xhr, status, e) {
                                                     if (! $.browser.msie) {
                                                         alert("Add subscription failed");
                                                     }
                                                 }
                                             ));
        addSub_aj.complete = function(xhr, status) {
            $("#newsubbox").val("");
        };
        

        var removeSub_aj = fK.fn.userPostObject({folksormsub: 1,
                                                 folksotag: undefined},
                                                rmSub,
                                                fK.fn.errorChoose(
                                                    notLogged,
                                                    function(xhr, status, e) {
//                                                    if (window.console) console.log(e);
                                                    alert(status + " Failed to remove subscription");
                                                    }
                                                ));

        getList_aj.dataType = "xml";
        getList_aj.cache = false;


        /*
         * Add subscription
         */
        $("#addsub").click(
            function(ev) {
                ev.preventDefault();
                var box = $("#newsubbox"), newtag = box.val();

                if (newtag.length == 0) {
                    alert("Il faut d'abord saisir un tag");
                }
                else {
                    addSub_aj.data.folksotag = newtag;                    
                    $.ajax(addSub_aj);
                    $.ajax(getRecently_aj);
                }
            });


        /*
         * Remove subscription
         */
        $("#subscriptions").delegate("a", "click",
                                     function(ev) {
                                         var $a = $(ev.target),
                                         parent = $a.parent();
                                         if ($a.hasClass("unsub")) {
                                             ev.preventDefault();
                                             removeSub_aj.data.folksotag  
                                                 = $("a.taglink", parent).text();
/*                                             if (window.console) {
                                                 console.log("going to remove");
                                                 console.log(removeSub_aj);
                                             }*/
                                             $.ajax(removeSub_aj);
                                             $.ajax(getRecently_aj);
                                         }
                                     });


        /*
         *  User intro controller (Bonjour X ! vous avez appliqué n tags)
         * 
         * This will be called from inside the #userdata controller to avoid 
         * multiple ajax calls for the same data. See userDataUpdateSuccess()
         */

        var W = new fK.Ktl("#user-intro");
        W.addBasic("username",
                   {selector: "span.userhello",
                    init: function (sel, $place, data) {
                        $(sel, $place).html(data);
                    },
                    update: function (sel, $place, data) {
                        $(sel, $place).html(data);
                    }
                   });

        W.addBasic("tagcount",
                   {selector: "#tagcount",
                    init: function (sel, $place, data) {
                        $(sel).html(data);
                    },
                    update: function (sel, $place, data) {
                        $(sel).html(data);
                    }
                    });

        var setWelcomeName = W.setfield("username"),
        setTagCount = W.setfield("tagcount");
        

        /*
         *   User data controller
         * 
         * It is assumed that #userdata has the correct structure
         * 
         */

        var U = new fK.Ktl("#userdata");
        U.addBasic("firstname",
                   {selector: "div.firstname",
                    init: function(sel, $place, data) {
                        $(sel + " span.firstname", $place).html(data);
                        $(sel + " input.firstnamebox", $place).val(data);
                    },
                    update: function (sel, $place, data) {
                        $(sel + " span.firstname", $place).html(data);
                        $(sel + " input.firstnamebox", $place).val(data);
                    },
                    deleteElem: function (sel, $place, data) {
                        ($sel + " span.firstname", $place).html('');
                        $(sel + "input.firstnamebox", $place).val('');
                    }
                   });

        U.addBasic("lastname",
                   {selector: "div.lastname",
                    init: function(sel, $place, data){
                        $(sel + " span.lastname", $place).html(data);
                        $(sel + " input.lastnamebox", $place).val(data);
                    },
                    update: function(sel, $place, data) {
                        $(sel + " span.lastname", $place).html(data);
                        $(sel + " input.lastnamebox", $place).val(data);
                    },
                    deleteElem: function(sel, $place, data) {
                        $(sel + " span.lastname", $place).html('');
                        $(sel + " input.lastnamebox", $place).val('');
                    }
                   });

        U.addBasic("email",
                   {selector: "div.email",
                    init: function(sel, $place, data){
                        $(sel + " span.email", $place).html(data);
                        $(sel + " input.emailbox", $place).val(data);
                    },
                    update: function(sel, $place, data) {
                        $(sel + " span.email", $place).html(data);
                        $(sel + " input.emailbox", $place).val(data);
                    },
                    deleteElem: function(sel, $place, data) {
                        $(sel + " span.email", $place).html('');
                        $(sel + " input.emailbox", $place).val('');
                    }
                   });

        U.addBasic("institution",
                   {selector: "div.institution",
                    init: function(sel, $place, data){
                        $(sel + " span.institution", $place).html(data);
                        $(sel + " input.institutionbox", $place).val(data);
                    },
                    update: function(sel, $place, data) {
                        $(sel + " span.institution", $place).html(data);
                        $(sel + " input.institutionbox", $place).val(data);
                    },
                    deleteElem: function(sel, $place, data) {
                        $(sel + " span.institution", $place).html('');
                        $(sel + " input.institutionbox", $place).val('');
                    }
                   });
        U.addBasic("pays",
                   {selector: "div.pays",
                    init: function(sel, $place, data){
                        $(sel + " span.pays", $place).html(data);
                        $(sel + " input.paysbox", $place).val(data);
                    },
                    update: function(sel, $place, data) {
                        $(sel + " span.pays", $place).html(data);
                        $(sel + " input.paysbox", $place).val(data);
                    },
                    deleteElem: function(sel, $place, data) {
                        $(sel + " span.pays", $place).html('');
                        $(sel + " input.paysbox", $place).val('');
                    }
                   });
        U.addBasic("fonction",
                   {selector: "div.fonction",
                    init: function(sel, $place, data){
                        $(sel + " span.fonction", $place).html(data);
                        $(sel + " input.fonctionbox", $place).val(data);
                    },
                    update: function(sel, $place, data) {
                        $(sel + " span.fonction", $place).html(data);
                        $(sel + " input.fonctionbox", $place).val(data);
                    },
                    deleteElem: function(sel, $place, data) {
                        $(sel + " span.fonction", $place).html('');
                        $(sel + " input.fonctionbox", $place).val('');
                    }
                   });

        U.addBasic("cv",
                   {selector: "div.user-cv",
                    init: function(sel, $place, data) {
                        $(sel + " div#cv-view", $place).html(data);
                        $(sel + " textarea.cv-write", $place).val(data);
                    },
                    update: function(sel, $place, data) {
                        $(sel + " div#cv-view", $place).html(data);

                        // contents to get inside the iframe, first and 
                        // only elem is html.
                        var $ifrHtml = $($("#cveditor_ifr").contents()[0]);
                        var $body = $("body", $ifrHtml);
                        $body.html(data);
                    },
                    deleteElem: function(sel, $place, data) {
                        $(sel + " div#cv-view", $place).html('');
                        var $ifrHtml = $($("#cveditor_ifr").contents()[0]);
                        var $body = $("body", $ifrHtml);
                        $body.html('');
                    }
                   });


        var 
        setFirstName = U.setfield("firstname"),
        setLastName  = U.setfield("lastname"),
        setEmail     = U.setfield("email"),
        setInstitution = U.setfield("institution"),
        setPays      = U.setfield("pays"),
        setFonction  = U.setfield("fonction"),
        setCv        = U.setfield("cv");


        var userDataUpdateSuccess = function(xml, status, xhr) 
        {
            if (xhr.status == 200) {
                $("#validerMessage").html("OK");
                var validerOkTimer = setTimeout(
                    function() {
                        $("#validerMessage").html("");
                    }, 7000);
                var firstname = $("firstname", xml).text(),
                lastname = $("lastname", xml).text(),
                fullname = firstname +  " " + lastname;

                setFirstName(firstname);
                setLastName(lastname);
                setEmail($("email", xml).text());
                setInstitution($("institution", xml).text());
                setPays($("pays", xml).text());
                setFonction($("fonction", xml).text());

                /*
                 * The jQuery.xml() method is provided by a plugin: 
                 * 
                 * http://plugins.jquery.com/project/x2s 
                 */
                setCv($("cv", xml).xml());

                $(U).trigger("update");
                
                /*
                 * Update fields defined in the W controller.
                 */ 
                setWelcomeName(fullname);
                setTagCount($("tagcount", xml).text());
                $(W).trigger("update");
            }
            else if (xhr.status == 204) {
/*
#ifdef DEBUG                 if (window.console) {
 console.debug("Treating 204 as not an error");
#endif
                }*/
            }
        };

        $("#userdata-send")
            .click(function(ev)
                   {
                       ev.preventDefault();
                       var tmp = $("body", $($("#cveditor_ifr").contents()[0])).html();
                       var pardiv = $(this).parent().parent(),
                       data = {
                           folksosetfirstname: $("input.firstnamebox", pardiv).val(),
                           folksosetlastname: $("input.lastnamebox", pardiv).val(),
                           folksosetemail: $("input.emailbox", pardiv).val(),
                           folksosetinstitution: $("input.institutionbox", pardiv).val(),
                           folksosetpays: $("input.paysbox", pardiv).val(),
                           folksosetfonction: $("input.fonctionbox", pardiv).val(),
                           folksosetcv: $("body", $($("#cveditor_ifr").contents()[0])).html()
                           };

                       $("#validerMessage").html("Envoi...");
                       $.ajax(fK.fn.userPostObject(data, userDataUpdateSuccess, 
                                                   fK.fn.errorChoose(
                                                       notLogged,
                                                       function() { 
                                                           $("#validerMessage").html("Error");
                                                           alert("error"); }
                                                       )));
                   });

        /**
         *  Called when we get a 204 when asking for user data.
         */
        var noUserData = function(xhr, textStatus, e) {
/*            if (window.console) {
                console.log("no user data. we are here"); 
            }*/
            $("#tag-brag").hide();
            $(".add-user-data").show();
        };
        noUserData.errorcode = "204";

        var getUser_aj = 
            fK.fn.userGetObject(
                {folksouserdata: 1},
                userDataUpdateSuccess,
                fK.fn.errorChoose(
                    notLogged,
                    noUserData,
                    function(xhr, tS, e) { 
                        if (! $.browser.msie) {
                            alert("Error retrieving user data. " +  xhr.status);
                        }
                    }
                ));
        getUser_aj.dataType = "xml";
        getUser_aj.cache = false;
        getRecently_aj.cache = false;


        /*
         * Favorite tags (designed to be read-only, thus read-once: no
         *  deletes or updates)
         */

        var Fav = new fK.Ktl("#favtags");
        Fav.addList("favorites",
                    {selector: "ul",
                     init: function(sel, $place, data) {
                         $(sel, $place)
                         .append($("<li><a class='taglink' href='" + data.link +
                                   "'>" + data.tagdisplay + " " + // to allow for linebreaks
                                   "</a></li>"));
                     },

                     match: function(data) {
                         return function( item, i) {
                             return item.link = data.link;
                         };
                     }
                    });

        /* 204 response:
         * 
         */
        var noFaves = function (xhr, textStatus, e) {


        };
        noFaves.errorcode = 204;


        var appendFave = Fav.appendField("favorites"),

        /*
         * Ajax success
         */
        gotFaves = function (json, status, xhr) {
            if (xhr.status == 200) {
                var len = json.length;
                for (var i = 0; i < len; ++i) {
                    appendFave(json[i]);
                }
                $(Fav).trigger("update");
            }
            else if (xhr.status == 204) {
                // nothing here yet
            }
        },


        getFaves_doAj = function () {
            if ($("li", $("#favtags")).length == 0) {
                $.ajax(
                    fK.fn.userGetObject(
                        {folksofavorites: 1},
                        gotFaves,
                        fK.fn.errorChoose(noFaves, notLogged, // does not work here because we 
                                          // are asking for json, not xml...
                                          function(xhr, textStatus, e) {
                                              if (! $.browser.msie) {
                                                  alert("Problem getting favorites: " + textStatus);
                                              }
                                          })
                ));
            }
        };

//       $(fK.events).unbind('loggedIn');
       $(fK.events).bind('loggedIn',
                         function() {
                             /* CLAG(console.debug("loggedIn just got triggered. " +
                              " loginStatus was " + fK.loginStatus);) */
                             $(".login-only").show();
                             $("h1.not-logged").hide();
                           $("#fbstuff").hide();
                           $("#login-tabs").hide();
                           $.ajax(getList_aj);
                           $.ajax(getRecently_aj);
                           $.ajax(getUser_aj);
                           getFaves_doAj();
                           fK.loginStatus = true;
                       });

        $(fK.events).bind('FBlogin',
                          function() {
                              /* CLAG(console.log("FBlogin event fired");) */
                              $("#loggedVia").show();
                              $("#loginSource").html("Facebook");
                          });

        $(fK.events).bind('OIDlogin',
                          function() {
                              $("#loggedVia").show();
                              $("#loginSource").html("OpenID");
                          });


//        $(fK.events).unbind('loggedOut');
        $(fK.events).bind('loggedOut',
                       function() {
/*                           CLAG(console.debug("loggedOut just got triggered. " + 
                                        "loginStatus was " + fK.loginStatus);) */
                           $('div.login-only').hide();
                           $('p.login-only').hide();
                           $("h1.not-logged").show();
                           $("#fKTaginput").hide();
                           $("#login-tabs").show();
                           var $oidTab = $("#tabs-1");
                           if ($("ul", $oidTab).length === 0) {
                               $oidTab.append(fK.oid.providerList()); 
                           }
                       });

        /*
         *  Page load actions
         */



        /*
         * If we still aren't logged in, we should make sure the right
         * elements are visible on the page.
         */
        if (fK.loginStatus === false) {
            $(fK.events).trigger('loggedOut');
        }

    });


fK.tinymceInit = 
    function () {
        var $cveditor = $("textarea#cveditor:first"); 
        var sibs = $cveditor.siblings("span.mceEditor").length;
        if (sibs === 0){
            if ($cveditor.is(":hidden")) {
                $cveditor.show();
            }
            LOG(console.log("initializing tinymce");)
            $cveditor.tinymce({
                                  script_url: '/tags/js/tinymce/jscripts/tiny_mce/tiny_mce.js',
/*                                  theme: "advanced",
                                  theme_advanced_toolbar_location: "top",
                                  theme_advanced_disable: "strikethrough,outdent,code,anchor,forecolor,backcolor,newdocument,visualaid,sub,image,indent,styleselect,formatselect",
                                  theme_advanced_buttons1: "bold,italic,underline,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist",
                                  theme_advanced_buttons2: "link,unlink,cleanup,hr,removeformat,superscript,charmap",
                                  theme_advanced_buttons3: "",
                                  valid_elements: "a[href],strong/b,em/i,ul,li,p,h2,h3,h4",
                                  width: "500", */

                             /*
                              * It is crucial to use raw here. Otherwise 
                              * the system will choke on the "&"
                              */
                             entity_encoding: 'raw'
                         });
        }
};