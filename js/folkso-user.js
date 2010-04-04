

/* subscribed tags Kontroler */

$(document).ready(
    function()
    {

        var hostAndPath = 'http://localhost/';
//        var hostAndPath = 'http://www.fabula.org/tags/';
        fK.init({
                    autocompleteUrl: hostAndPath + 'tagcomplete.php',
                    postResUrl: hostAndPath + 'resource.php',
                    getResUrl: hostAndPath + 'resource.php',
                    getUserUrl: hostAndPath + 'user.php',
                    oIdLogoPath: "/tags/logos/",
                    oIdPath: '/tags/fdent/'
                });

        $("input.fKTaginput", fK.cf.container).autocomplete(fK.cf.autocompleteUrl);       
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
        unsubTag = K.deletefield("subscribed");

        /**
         * "Success" function for retrieval of a list of current
         *  subscribed tags
         */
        var gotList = function (xml, status, xhr) 
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
            console.log("I got removed");
            var tag = {};
            tag.numid = $("numid", xml).text();
            tag.tagnormm = $("tagnorm", xml).text();
            tag.link = $("link", xml).text();
            tag.display = $("display", xml).text();
            unsubTag(tag);
            $(K).trigger("update");
        };

        var cesspool = "e470b9de7ed755b752ad8bae1c618fe4216ff508bfd0375b03e98df0af2e475d";
        var getList_aj = fK.fn.userGetObject({folksosubscribed: 1,
                                              folksosession: cesspool},
                                             gotList,
                                             function(xhr, textStatus, e) { 
                                                 if (xhr.status != 204) {
                                                     alert("list getting failed"); 
                                                 }
                                             });

        // tag needs to be defined before using this
        var addSub_aj = fK.fn.userPostObject({folksoaddsubscription: 1,
                                              folksosession: cesspool,
                                              folksotag: undefined},
                                             gotList,
                                             function(xhr, status, e) {
                                                 alert("Add subscription failed");
                                             });
        addSub_aj.complete = function(xhr, status) {
            $("#newsubbox").val("");
        };
        

        var removeSub_aj = fK.fn.userPostObject({folksormsub: 1,
                                                 folksosession: cesspool,
                                                 folksotag: undefined},
                                                rmSub,
                                                function(xhr, status, e) {
                                                    console.log(e);
                                                    alert(status + " Failed to remove subscription");
                                                });

        getList_aj.dataType = "xml";
        $.ajax(getList_aj);


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
                                             console.log("going to remove");
                                             console.log(removeSub_aj);
                                             $.ajax(removeSub_aj);
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
                           folksosetfonction: $("input.fonctionbox", pardiv).val(),
                           folksosession: cesspool
                           };

                       $.ajax(fK.fn.userPostObject(data, userDataUpdateSuccess, 
                                                   function() { alert("error"); }));
                   });
        var getUser_aj = 
            fK.fn.userGetObject(
                {folksouserdata: 1, folksosession: cesspool },
                userDataUpdateSuccess,
                function() { alert("something went haywire"); });
        getUser_aj.dataType = "xml";
        $.ajax(getUser_aj);

    });
