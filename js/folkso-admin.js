
$(document).ready(
    function()
    {

        var hostAndPath = '/tags/';
        fK.init({
                    autocompleteUrl:    hostAndPath + 'tagcomplete.php',
                    postResUrl:         hostAndPath + 'resource.php',
                    getResUrl:          hostAndPath + 'resource.php',
                    getUserUrl:         hostAndPath + 'user.php',
                    getAdminUrl:        hostAndPath + 'admin.php',
                    postAdminUrl:       hostAndPath + 'admin.php'
                });



        var U = new fK.Ktl("#userlist");
        U.addList("users",
                  {selector: "ul",
                   init: function(sel, $place, data) {
                       $(sel, $place)
                       .append(formatUserListItem(data));
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

        /**
         * On successful AJAX request for complete list.
         */
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



        /** Formatting functions **/


        /**
         * Formats a list item. Returns complete list item as a
         * string, including the <li>.
         */
        var formatUserListItem =
            function(data) {
                var ar =
                ["<li>" ,
                 "<p class='identity'>",
                 "<span class='realname'>",  data.firstname, " " ,
                 data.lastname,  "</span> ",
                 "<span class='userid'>", data.userid, "</span>",
                 "</p>",

                 "<ul> class='details>",
                 "<li><span class='detail-category'>Email : </span>",
                 "<span class='detail-data'>",
                 data.email,
                 "</span></li>",
                 "<li><span class='detail-category'>Institution : </span>",
                 "<span class='detail-data'>",
                 data.institution,
                 "</span></li>",
                 "<li><span class='detail-category'>Pays : </span>",
                 "<span class='detail-data'>",
                 data.pays,
                 "</span><li>",
                 "<li><span class='detail-category'>Fonction : </span>",
                 "<span class='detail-data'>",
                 data.fonction,
                 "</span><li>",
                 "<ul>",
                 "</li>"];

                return ar.join("");

            };


    });