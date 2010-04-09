

/* subscribed tags Kontroler */

$(document).ready(
    function()
    {

//        var hostAndPath = 'http://localhost/';
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
        $("#newsubbox").autocomplete(fK.cf.autocompleteUrl);     


        /*
         * Do nothing: basic error function for when ajax calls return no data.
         */
        var err204 = fK.fn.defErrorFn(204, function() { });


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
                                    function() {
                                        alert("Resource list problem");
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


        var getList_aj = fK.fn.userGetObject({folksosubscribed: 1},
                                             gotList,
                                             fK.fn.errorChoose(err204,
                                                               function(xhr, textStatus, e) { 
                                                                   alert("list getting failed"); 
                                                 })
                                             );

        // tag needs to be defined before using this
        var addSub_aj = fK.fn.userPostObject({folksoaddsubscription: 1,
                                              folksotag: undefined},
                                             addListItem,
                                             function(xhr, status, e) {
                                                 alert("Add subscription failed");
                                             });
        addSub_aj.complete = function(xhr, status) {
            $("#newsubbox").val("");
        };
        

        var removeSub_aj = fK.fn.userPostObject({folksormsub: 1,
                                                 folksotag: undefined},
                                                rmSub,
                                                function(xhr, status, e) {
//                                                    if (window.console) console.log(e);
                                                    alert(status + " Failed to remove subscription");
                                                });

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
         *   User data controller
         * 
         * It is assumed that #userdata has the correct structure
         * 
         */

        var U = new fK.Ktl("#userdata");
        U.addBasic("firstname",
                   {selector: "p.firstname",
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
                   {selector: "p.lastname",
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
                   {selector: "p.email",
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
                   {selector: "p.institution",
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
                   {selector: "p.pays",
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
                   {selector: "p.fonction",
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


        var 
        setFirstName = U.setfield("firstname"),
        setLastName  = U.setfield("lastname"),
        setEmail     = U.setfield("email"),
        setInstitution = U.setfield("institution"),
        setPays      = U.setfield("pays"),
        setFonction  = U.setfield("fonction");


        var userDataUpdateSuccess = function(xml, status, xhr) 
        {
            if (xhr.status == 200) {
                setFirstName($("firstname", xml).text());
                setLastName($("lastname", xml).text());
                setEmail($("email", xml).text());
                setInstitution($("institution", xml).text());
                setPays($("pays", xml).text());
                setFonction($("fonction", xml).text());

                $(U).trigger("update");
            }
        };

        $("#userdata-send")
            .click(function(ev)
                   {
                       ev.preventDefault();
                       var pardiv = $(this).parent().parent(),
                       data = {
                           folksosetfirstname: $("input.firstnamebox", pardiv).val(),
                           folksosetlastname: $("input.lastnamebox", pardiv).val(),
                           folksosetemail: $("input.emailbox", pardiv).val(),
                           folksosetinstitution: $("input.institutionbox", pardiv).val(),
                           folksosetpays: $("input.paysbox", pardiv).val(),
                           folksosetfonction: $("input.fonctionbox", pardiv).val()
                           };

                       $.ajax(fK.fn.userPostObject(data, userDataUpdateSuccess, 
                                                   function() { alert("error"); }));
                   });
        var getUser_aj = 
            fK.fn.userGetObject(
                {folksouserdata: 1},
                userDataUpdateSuccess,
                function() { alert("Error retrieving user data"); });
        getUser_aj.dataType = "xml";
        getUser_aj.cache = false;
        getRecently_aj.cache = false;




        /*
         *  Page load actions
         */

        if (fK.loginStatus !== false) {
//            if (window.console) console.log("loginStaus ok");
            $("h1.not-logged").hide();
            $.ajax(getList_aj);
            $.ajax(getRecently_aj);
            $.ajax(getUser_aj);
        }
        else {
  //          if (window.console) console.log("loginStaus not ok, hiding stuff");
            $("div.login-only").hide();
            $("#fbstuff").show();
        }

        $('body').bind('loggedIn',
                       function() {
                           $("div.login-only").show();
                           $("h1.not-logged").hide();
                           $.ajax(getList_aj);
                           $.ajax(getRecently_aj);
                           $.ajax(getUser_aj);
                       });
    });