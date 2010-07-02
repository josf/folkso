
(function(){
     var fK = window.fK;
     fK.admin = {
         /** Formatting functions **/


         /**
          * Formats a list item. Returns complete list item as a
          * string, including the <li>.
          */
         formatUserListItem:
         function(data) {
             data = fK.admin.removeNullFields(data);

             var ar =
                 ["<li>" ,
                  "<p class='identity'>",
                  "<span class='realname'>",  data.firstname, " " ,
                  data.lastname,  "</span> ",
                  "<span class='userid'>", data.userid, "</span>",
                  "</p>",
                  '<div class="userrights">',
                  '<span class="detail-category">Niveau d\'accès :</span> ',
                  
                  fK.admin.rightRadioButtons(data.rights),

                  "<ul class='details'>",
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
                  "</span></li>",
                  "<li><span class='detail-category'>Fonction : </span>",
                  "<span class='detail-data'>",
                  data.fonction,
                  "</span></li>",
                  "<li><span class='detail-category tagcount'>Actes de taggage :</span>",
                  "<span class='detail-data'>",
                  data.tagcount,
                  "</li>",

                  "</ul>",
                  "<p><a class='delete-user' href='#'>Supprimer l'utilisateur</a></p>",
                  "</li>"];

             return ar.join("");

         },

         removeNullFields:
         function(data) {
             for (var prop in data) {
                 if ((data[prop] === 'NULL') ||
                     (data[prop] == "0")){
                     data[prop] = '';
                 }
             }
             return data;
         },

         /*
          * This would need to be updated if we have more than 3 types
          * of privileges.
          */
         maxRight:
         function(arr) {
             if (arr.length === 1 && 
                 (arr[0] === 'admin') ||
                 (arr[0] === 'redac')) {
                 return arr[0];
             }
             else {
                 var best;
                 for (var i = 0; i < arr.length; i++) {
                     if (arr[i] === 'admin') {
                         return 'admin';
                     }
                     else if (arr[i] === 'redac') {
                         best = 'redac';
                     }
                 }
                 return best; // if neither redac nor admin, best is empty
             }
         },

         /* Returns string for the rights part. Takes the list of rights (data.rights)
          * 
          */
         rightRadioButtons:
         function(rightlist) {
             var 
             highestRight = rightlist ? fK.admin.maxRight(rightlist) : 'user',
             html = 
                 '<span class="currentright">' 
                 + highestRight
                 + '</span>'
                 + '<div class="rightmod">Modifier: ',

             rights = ['user', 'redacteur', 'admin'];

             if (highestRight === 'redac') {
                 highestRight = 'redacteur';
             }

             for (var i = 0; i < 3;i++) {
                 var closing = 
                     (rights[i] === highestRight) ? ' checked="checked"/>' : '/>';
                 html = 
                     html 
                     + '<input type="radio" name="rightmod" value="'
                     + rights[i] + '"'
                     + closing + " " + rights[i] + " ";
             }
             html = html + '<a href="#" class="rightModButton">Modifier</a></div>';
             return html;
         },

         /*
          * Handler for all clicks in the user data fields. 
          */
         userModClickHandler:
         function (ev) {
             /**
              * $list is the entire list output by formatUserListItem()
              */
             var $target = $(ev.target), $list = $("#userlist");

             if ($target.hasClass("rightModButton")) {
                 ev.preventDefault(); 
                 fK.admin.rightMod_aj($target, $list);
             }
             else if ($target.is("a.delete-user")) {
                     ev.preventDefault();
                     fK.admin.delUser($target, $list);
             }
         },

         /*
          * @return Array List of strings naming the user's rights. 
          * @param xml Any xml that contains only one user rights list. Can also be a jQuery object.
          * 
          * You cannot call this on an entire user list.
          * 
          * Empty array means "user". 
          */
         parseUserRights: 
         function (xml) {
             var $xml = xml.jquery ? xml : $(xml), rights = ["user"]; 
             if ($("right", $xml).length > 0) {
                 $("right", $xml).each(
                     function() {
                         rights.push($("type", $(this)).text()); 
                     });
             }
             return rights;
         },

         rightMod_aj:
         function($button, $list) {
             var 
             rightRadioButtons = $button.closest("div.rightmod")
                 .find("input"),
             newRight = $button.closest("div.rightmod")
                 .find("input:checked").val();
             
             if (newRight === 'redacteur') {
                 newRight = 'redac';
             }
             var aj_ob = fK.fn.adminPostObject(
                 {folksouser: $("span.userid", $list).text(),
                  folksonewright: newRight},

                 function(xml, status, xhr) {
                     var rights = fK.admin.parseUserRights(xml);
                     $button.closest("div.userrights").find("span.currentright")
                         .text(rights[rights.length - 1]);
                 },

                 function() {
                     alert("Failed to update right");
                 });
             //                aj_ob.dataType = 'text';
             jQuery.ajax(aj_ob);
         },

         delUser: 
         function ($target, $list) {

             var 
             userid = $("span.userid", $list).text(),
             username = $("span.realname", $list).text(),
             tagcount = $("span.tagcount", $list).text();

             /* prepare the ajax function first */
             var delUser_aj = 
                 function () {
                     var aj_ob =
                         fK.fn.adminPostObject(
                             {folksodelete: "1",
                              folksouser: userid},

                             /* success */
                             function() {
                                 $list.hide();
                             },
                         
                             /* failure */
                             function(xhr, textStatus, errThrown) {
                                 alert("Error: " + textStatus);
                             }
                         );
                     aj_ob.dataType = "text";
                     jQuery.ajax(aj_ob);
                 };

             /*
              * Confirmation box
              */
             var diaString = "<p>L'utilisateur <span class='deluser-name'>" 
                 + username + "</span>"
                 + " sera supprimé définitivement. ";

             if (tagcount > 0) {
                 diaString += tagcount;
                 diaString += " tags (actes de taggage) sont associés à ce ";
                 diaString += " compte utilisateur. Ils seront suppprimés également. ";
             }
             diaString += "</p>";

             var $dia = $(diaString);
             $list.append($dia);

             /* prepare the dialog box */
             $dia.dialog( {title: "Supprimer un compte utilisateur",
                           modal: true,
                           buttons: {
                               "Abandoner": function () {
                                   $dia.dialog("close");
                               },
                               "Supprimer": function() {
                                   delUser_aj();
                                   $dia.dialog("close");
                               }
                           }
                          });
         }
     };

})();




$(document).ready(
    function()
    {

        var f = fK.admin; //shortcut for our functions

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
                  {selector: "ul#masterlist",
                   init: function(sel, $place, data) {
                       $(sel, $place)
                       .append($(f.formatUserListItem(data))
                              .click(f.userModClickHandler));
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
                        user.tagcount   = $("tagcount", $usob).text();

                        user.rights = f.parseUserRights($usob);
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
                    var $currentList = $("ul", "#userlist");
                    getUsers_aj.data.folksosearch = searchtext;
                    $.ajax(getUsers_aj);
                }
            });

        
    });

