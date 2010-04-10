
$(document).ready(
    function()
    {

        var hostAndPath = '/tags/';
        fK.init({
                    autocompleteUrl:    hostAndPath + 'tagcomplete.php',
                    postResUrl:         hostAndPath + 'resource.php',
                    getResUrl:          hostAndPath + 'resource.php',
                    getUserUrl:         hostAndPath + 'user.php'
                });



        var U = new fK.Ktl("#userlist");
        U.addList("users",
                  {selector: "ul",
                   init: function(sel, $place, data) {
                       $(sel, $place)
                       .append($("<li>" + data.firstname + " " + data.lastname 
                                 + "</li>"));
                   },
                   match: function(data) {
                       return function (item, i) {
                           return item.userid = data.userid;
                       };
                   }
                  });

        var 
        appendUser =            U.appendField("users"),
        dropUserList =      U.restartList("users"),

        gotUserList = function(xml, status, xhr)
        {
            dropUserList();
            $("user", xml)
                .each(
                    function () 
                    {
                        var user = {}, $usob = $(this);
                        user.userid     = $("userid", $usob).text();
                        user.firstname  = $("firstname", $usob).text();
                        user.lastname   = $("lastname", $usob).text();
                        user.nick       = $("nick", $usob).text();
                        user.email      = $("email", $usob).text();
                        user.institution = $("institution", $usob).text();
                        user.pays       = $("pays", $usob).text();
                        user.fonction   = $("fonction", $usob).text();

                        if ($("right", $usob).length > 0) {
                            user.rights = [];
                            $("right", $usob)
                                .each(
                                    function () {
                                        user.rights.push($("type", $(this)).text());   
                                    });
                        }

                        appendUser(user);
                    });
            $(U).trigger("update");
        },

        
        // NB: folksosearch field needs to be completed before sending
        getUsers_aj = 
            fK.fn.adminGetObject({folksosearch: null},
                                 gotUserList,
                                 fK.fn.errorChoose(
                                     fK.fn.defErrorFn(204,
                                                      function() {
                                                          dropUserList();
                                                      }),
                                     function(xhr, status, e) {
                                         alert("User list problem: " 
                                               + xhr.status + " " 
                                               + xhr.statusMessage);
                                     }
                                 ));
        
        /*
         * search box
         */
        var searchbox = $("#searchbox"), searchok = $("#searchok");
        
        searchok.click(
            function(ev) {
                ev.preventDefault();
                var searchtext = searchbox.val();                
                if ($.trim(searchtext).length == 0) {
                    alert("Vous devez saisir une requête d'abord");
                }
                else {
                    getUsers_aj.data.folksosearch = searchtext;
                    $.ajax(getUsers_aj);
                }
            });



    });