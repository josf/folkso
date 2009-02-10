
jQuery.fn.extend({

                   /**
                    * Get the parent resource id (resid) from any element contained
                    * inside a <li>, or from the <li> itself. Can be either an numeric
                    * id (prefered) or a url.
                    *
                    */
                   resid : function() {
                     var lis;

                     /* check to see if this is already the "li" item */
                     if ((this.is("li.resitem") ||
                          (this.is("li.tagged")) ||
                          (this.is("li.nottagged")))) {
                       lis = this;
                     }
                     /* otherwise look at ancestors */
                     else {
                       var pars = this.parents("li.resitem, li.tagged, li.nottagged");
                       if (pars.length == 0) {
                         return ''; /** error instead? **/
                       }
                       else {
                       lis = $(pars[0]);
                       }
                     }
                     if (lis.attr("id") && (lis.attr("id").length > 3)) {
                       return lis.attr("id").substring(3);
                     }
                     else {
                       // backup plan, but will provide url instead of id
                       return lis.find("a.resurl").attr("href");
                      }
                    },

                   tagitem: function() {
                     if (this.is("li.tagitem")) {
                       return this;
                     }
                     var pars = this.parents("li.tagitem");
                     if (pars.length == 0){
                       return null;
                     }
                     else {
                       return $(pars[0]);
                     }
                   },

                   /**
                    * Find the current <li class="resitem">
                    */

                   resitem: function() {
                     if ((this.is("li.resitem")) ||
                         (this.is("li.tagged")) ||
                         (this.is("li.nottagged"))) {
                       return this;
                     }
                     var pars = this.parents("li.resitem, li.tagged, li.nottagged");
                     if (pars.length == 0){
                       return null;
                     }
                     else {
                       return $(pars[0]);
                     }
                   },

                   tagid: function() {
                     var titem  = this.tagitem();
                     var ret;
                     if (titem.find("span.tagid").length > 0) {
                        ret = titem.find("span.tagid").text();
                       }
                     return ret;
                     }
                 }); /** end of jQuery extend


 /**
  *  "this" must be a list element from the main list of resources.
  */
 function iframePrepare() {
   var lis = $(this);
   var url = lis.find("a.resurl").attr("href");
   var holder = lis.find("div.iframeholder");
   lis.find("a.openiframe").click(
     function(event) {
       event.preventDefault();

       if (holder.find("iframe").length) {
         holder.show();
       }
       else {
         var ifr = document.createElement("iframe");
         ifr.src = url;
         ifr.className = "preview";
         holder.append(ifr);
         holder.show();
       }
       $(this).hide();
       if (lis.find("a.closeiframe").length < 2) {
         holder.append(lis.find("a.closeiframe").clone());
         holder.find("a.closeiframe").click(
           function(event) {
             event.preventDefault();
             holder.hide();
             holder.parent("li").find("a.closeiframe").hide();
             holder.parent("li").find("a.openiframe").show();
           });
       }
       lis.find("a.closeiframe").show();
     }
   );

   $(this).find("a.closeiframe").click(
     function(event) {
       event.preventDefault();
       holder.hide();
       $(this).hide();
       $(this).parent().parent().find("a.closeiframe").hide();
       lis.find("a.openiframe").show();
     }
   );
 }

function tagMenuCleanupFunc(lis, tag) {
  return function() {
    lis.attr("class", "tagged");
    lis.find("inbox.tagbox").val('');
    currentTagsUpdate(tag, lis);
  };
}

 /**
  * To be called on a ul.tagmenu
  */
 function tagremovePrepare() {
   var remove = $(this).find("a.remtag");

   var taglistdiv = $(this).parent();
   var resourceid = $(this).resid();

   remove.click(function(event) {
         event.preventDefault();
         var tagid = $(this).siblings(".tagid").text();
         $.ajax({
             url: document.folksonomie.postbase + 'resource.php',
             type: 'post',
             data: {
               folksores: resourceid,
               folksotag: tagid,
               folksodelete: 1
             },
             error: function(xhr, msg) {
               alert(msg);
             },
             success: function(data) {
               getTagMenu(
                 taglistdiv,
                 resourceid
                 );
             }
             });
         });
 }
 /**
  * To be called on a list element. Does not just hide, shows as well!
  */
 function taglistHidePrepare() {
   var lis = $(this);
   var resid = lis.resid();

   lis.find("a.seetags").click(
     function(event) {
       event.preventDefault();
       getTagMenu(lis.find("div.emptytags"), resid);
       $(this).text("Actualiser");
       $($(this).siblings("a.hidetags")[0]).show();
     }
   );

   lis.find("a.hidetags").click(
     function(event) {
       event.preventDefault();
       $($(this).siblings("a.seetags")[0]).text("Voir");
       lis.find("ul.tagmenu").remove();
       $(this).hide();
       }
   );
 }

 /**
  * Create or update a tag menu. (Most of the work is done
  * by tagMenuFromXml.)
  */
 function getTagMenu(place, resid) {
//     place.find("ul.tagmenu li").hide();
   if (! resid) {
     alert("No resid for getTagMenu!");
   }
   var tagMenuFromXmlFunction = tagMenuFunkMaker(place, resid);
    $.ajax({ url: document.folksonomie.getbase + 'resource.php',
           type: 'get',
           datatype: 'text/xml',
           data: {
             folksores: resid,
             folksoean13: '1',
             folksodatatype: 'xml'},
           success: tagMenuFromXmlFunction,
           error: function(xhr, msg) {
             alert("An error here: " + xhr.statusText);
           }});
}

/**
 * Returns a function closed over "place", allowing us to get
 * to this variable when called without arguments in the $.ajax call.
 *
 */
function tagMenuFunkMaker(place, resid) {
  var dest = place;
  return function(xml) {
    var ul = $('<ul class="tagmenu">');
    $("taglist tag", xml).each(
      function() {
        var item = $('<li class="tagitem">');
        var metatype = $(this).find('metatag').text();

        if (metatype == 'EAN13') {
          makeEan13TagMenuItem($(this), item, resid);
        }
        else {
          makeStandardTagMenuItem($(this), item, metatype, resid, place);
        }
        ul.append(item);
      });

    /** Now that we have our new list, put it back into the DOM **/
    if (dest.find("ul.tagmenu").length) {
      dest.find("ul.tagmenu").replaceWith(ul);
    }
    else {
      dest.append(ul);
    }
    place.find("ul.tagmenu").each(tagremovePrepare);
  };
}

/**
 * xml_tag is a jQuery object.
 * tlis is a jQuery <li>.
 * metatype is a string (we pass it as an arg to avoid getting it again
 * from tlis.
 *
 * Returns the <li> built from tlis.
 */
function makeStandardTagMenuItem(xml_tag, tlis, metatype, resid, place) {
  var taglink = $('<a>');
  taglink.attr("href", tagUrl(xml_tag.find("numid").text()));
  taglink.attr("class", "tagdisplay");
  taglink.append(xml_tag.find('display').text() + ' ');
  tlis.append(taglink);
  /** add tag id **/
  tlis.append($("<span class='tagid'>"
                + xml_tag.find('numid').text()
                + "</span>"));
  tlis.append($('<a class="remtag" href="#">Désassocier</a>'));
  /** meta tag (if not "normal") **/

  if (metatype != 'normal') {
    tlis.append($("<span class='meta'> Relation: "
                  + metatype
                  + "</span>"));
  }

  tlis.append(makeMetatagBox(resid,
                             xml_tag.find('numid').text(),
                             place));
  return tlis;
}

/**
 * Used when building the ajax tag menus.
 *
 *  xml_tag is a jQuery object.
 *  tlis is a jQuery <li>
 */
function makeEan13TagMenuItem(xml_tag, tlis, resid) {
  var ean13number = xml_tag.find("numid").text();
  tlis.append("<span class=\"ean13flag\">EAN13 / ISBN:  </span>");
  var modEan13Func = makeEan13PostModFunc(ean13number,
                                          resid,
                                          function () { // what happens after the ajax post
                                            var inp = tlis.find("input.ean13correctbox");
                                            var newean = inp.val();
                                            inp.replaceWith("<span>" + newean + "</span>");
                                            tlis.find("a.ean13modbutton").remove();
                                          });

  /** eandisplay is clickable text that can be used to modify the data **/
  var eandisplay = $('<span>' + ean13dashDisplay(ean13number) + '</span>');
  eandisplay.attr("class", "ean13tagdisplay");
  eandisplay.click(
    function(event){
      $(this).after($("<a href=\"#\" class=\"ean13modbutton\">"
                      + "Modifier</a>").click(
                        function (event) {
                          event.preventDefault();
                          /** argument here is the val() in the input box **/
                          var eanbox = $($(this).siblings("input.ean13correctbox")[0]);
                          var newean = eanbox.val();
                          if (ean13validate(newean)){
                            if (ean13clean(newean)  == ean13clean(ean13number)){
                              alert("Les deux EAN-13 sont identiques");
                            }
                            else{
                              modEan13Func(newean);
                            }
                          }
                          else {
                            alert("L'EAN-13 proposé semble incorrect.");
                            eanbox.val(ean13number);
                          }
                        }));

      $(this).replaceWith($("<input type=\"text\" "
                   + "class=\"ean13correctbox\" size=\"17\" "
                   + "maxlength=\"17\" value=\""
                   + ean13dashDisplay(ean13number)
                            + "\"/>"));
    });
  tlis.append(eandisplay);
  tlis.append($("<span class=\"blankspace\"> </span>"));

    /** suppression **/
  var eandelete = $('<a class="remean13" href="#">Supprimer </a>').click(
    function(event) {
      event.preventDefault();
      var tagitem = $(this).tagitem();
      $.ajax({
               url: document.folksonomie.postbase + 'resource.php',
               type: 'post',
               data: {
                 folksores: $(this).resid(),
                 folksodelete: 1,
                 folksoean13: ean13clean(
                   $($(this).siblings("span.ean13tagdisplay")[0]).text()
                   )
               },
               success: function(data) {
                 tagitem.remove();
               },
               error: function(xhr, msg) {
                 alert("Problem removing ean-13 "
                       + xhr.status + " " + xhr.statusText + " " + msg);
               }
             });
    });
  tlis.append(eandelete);
  return tlis;
}

function makeEan13PostModFunc(ean13number, resid, onsuccess) {
  return function(newEan) {
    $.ajax({
             url: document.folksonomie.postbase + 'resource.php',
             type: 'post',
             data: {
               folksores: resid,
               folksooldean13: ean13clean(ean13number),
               folksonewean13: ean13clean(newEan)
             },
             error: function(xhr, msg){
                 alert("Error modifying ean13 data: " + msg + " status "
                      + xhr.statusText);
             },
             success: onsuccess
           });
  };
}

/**        postNewEan13($(this)); **/

/**
 * button is the a.ean13addbutton as a $();
 */

function postNewEan13(button) {
  var inp = $(button.siblings("input.ean13addbox")[0]);
  var ean13 = inp.val();
  if (! ean13validate(ean13)){
    alert("Le code EAN13 proposé n\'est pas correcte. ["
      + ean13clean(ean13) + "] " + ean13clean(ean13).length );
    return null;
  }
  var lis = $(button.parents("li.resitem")[0]);
  var resid = button.resid();

  return  $.ajax({
           url: document.folksonomie.postbase + 'resource.php',
           type: 'post',
           data: {
             folksores: resid,
             folksoean13: ean13clean(ean13)
           },
           success: function(data) {
             var cean = lis.find("span.currentean13");
             if (cean.text().length > 0) {
               cean.text(cean.text() + ", " + ean13dashDisplay(ean13));
             }
             else {
               cean.text(ean13dashDisplay(ean13));
             }
             lis.find("input.ean13addbox").val("");
           },
           error: function(xhr, msg) {
             if (xhr.status == 409) {
               alert("Le numéro EAN13 " + ean13
                     + " est déjà associé à cette ressource.");
             }
               else {
                 alert("Error posting new ean13 "
                 + msg + " status " + xhr.status
                 + " " + xhr.statusText);
               }
           }
         });
}


function ean13validate(ean){
  var clean = ean13clean(ean);
  if ((clean.length == 13) &&
      (clean.match(/^\d+$/)) &&
      (ean13ValidateChecksum(clean))) {
      return true;
    }
    else {
      return false;
    }
}

function ean13ValidateChecksum (num) {
  var eanS = num.toString();
  if (ean13Checksum(num) ==  parseInt(eanS.slice(12,13))){
    return true;
  }
  else {
    return false;
  }
}

function ean13Checksum (ean) {
  var eanS = ean.toString();
  var douze = eanS.slice(0, 12);
  var product = 0;
  var pos = 0;

  for (var it = 0; it < douze.length; ++it){
    pos = it + 1;
    if (oddp(pos)) {
      product = parseInt(douze.charAt(it)) + product;
    }
    else {
      product = (parseInt(douze.charAt(it)) * 3) + product;
    }
  }
  if ((product % 10) == 0) {
    return 0;
  }
  else {
    return 10 - (product % 10);
  }
}

function oddp (num) {
  if ((num % 2) == 1) {
    return true;
  }
  else {
    return false;
  }
}



  /**
   * just a stub, so that we can format ean13 urls when it comes to
   * that.
   */

function ean13url(ean) {
  return ean;
}

function ean13clean(dirty) {
  return dirty.replace(/[^0-9]/g, '');
}

function ean13dashDisplay(num) {
  var ean;
  if (num.length == 13) {
    ean =
      num.slice(0,3) + "-"
    + num.slice(3,12) + "-"
    + num.slice(12, 13);
  }
  else if (num.length == 10) {
    ean =
        num.slice(0,9) + "-"
      + num.slice(9,10);
    }
   else { //error here but we just pass it on and prepend a '!'
     ean = "!" + num;
   }
  return ean;
}



function metatagDropdown (list, boxclass) {
  var theclass;
  if ((typeof boxclass == "string") &&
    (boxclass.length > 0)){
    theclass = boxclass;
  }
  else {
    theclass = "metatagbox";
  }

  var box = $("<select class='" + theclass + "'>");
  box.append("<option></option>"); // empty first choice
  for (var i = 0; i <
       document.folksonomie.metatag_autocomplete_list.length; i++) {
    box.append("<option>" +
      document.folksonomie.metatag_autocomplete_list[i] + "</option>");
  }
  return box;
}

/**
 *  Parameter lis is a list item so that we know where to plug the
 *  new taglist back in when we are done.
 *
 * resource and tag can be either text or ids.
 */
function makeMetatagBox (resource, tag, lis) {
  if (! tag) {
    alert("missing tag here in makemetatag box");
  }

  var container = $("<span class='metatagbox'></span>")
    .append("<span class='infohead'>Modifier le metatag </span>");
  //  box.autocomplete(metatag_autocomplete_list); //array defined in <script> on page.
  var box;
  if (document.folksonomie.metatag_autocomplete_list){
    box = metatagDropdown(
      document.folksonomie.metatag_autocomplete_list,
      "metatagbox");
  }
  else{
    box = $("<select class=\"metatagbox\"><option/></select>");
    box.each(metaSelectOptions);
  }

  var postmeta_f = (function(){
                     return function(newmeta){
                        $.ajax({
                                 url: document.folksonomie.postbase + 'resource.php',
                                 type: 'post',
                                 data: {
                                   folksores: resource,
                                   folksotag: tag,
                                   folksometa: newmeta
                                 },
                                 error: function(xhr, msg) {
                                   alert("metatag error " + msg);
                                 },
                                 success: function(data) {
                                   getTagMenu(lis, resource);
                                 }
                               });
                        };
                    })();

  var button = $("<a href='#' class='metatagbutton'>Ajouter métatag</a>")
    .click(
      function(event){
        event.preventDefault();
        var newmeta = $(this).siblings("select").val();
        postmeta_f(newmeta);
      });
   container.append(box);
   return container.append(button);
}

/**
 * Returns a function that will post a tag + res + metatag. For use in
 * a grouptag action where errors can be ignored.
 */
function groupTagPostFunc(res, tag, meta, clean) {
  $.ajax({
           url: document.folksonomie.postbase + 'resource.php',
           type: 'post',
           datatype: 'text/text',
           data: {
             folksores: res,
             folksotag: tag,
             folksometa: meta
           },
           success: clean
    });
}


function groupTag() {
  var newtag = $("#grouptagbox").val();
  var firstlis = $("input.groupmod:checked:first").parent().parent("li");
  var firstres = firstlis.attr('id').substring(3);
  var meta = $("#groupmetatagbox").val();

  $.ajax({
           url: document.folksonomie.postbase + 'resource.php',
           type: 'post',
           datatype: 'text/text',
           data: {
             folksores: firstres,
             folksotag: newtag,
             folksometa: meta
           },
           error: function(xhr, msg) {
             if (xhr.status == 404) {
               if (xhr.statusText.indexOf('Tag does not exist') != -1) {
                 var meta2 = meta;
                 infoMessage(
                   createTagMessage(newtag,
                                    firstres,
                                    '',
                                    firstlis,
                                    function() {
                                      alert("Création réussie du nouveau tag");
                                      $("#superscreen").hide();
                                      groupTagRest(newtag, meta);
                                      $("#grouptagbox").val('');
                                      }));
               }
               else {
                 alert("404 but no tag " + xhr.statusText);
               }
             }
             else {
               alert('something else');
             }
           },
           success: function(str) {
             groupTagRest(newtag, meta);
           }
         });
}

function groupTagRest(tag, meta) {
  $("input.groupmod:checked:not(first)").each(
    function() {

      var lis = $(this).parent().parent("li");
      var resid = lis.attr("id").substring(3);
      $.ajax({
               url: document.folksonomie.postbase + 'resource.php',
               type: 'post',
               datatype: 'text/text',
               data: {
                 folksores: resid,
                 folksotag: tag,
                 folksometa: meta
               },
               success: function(str){
                 lis.attr("class", "tagged");
                 currentTagsUpdate(tag, lis);
               }
             });
    });
}

function showSuperScreen() {
  var sscreen = $("#superscreen");
  var ibox = $("#superinfogox");

  if (sscreen.css("display") == "none") {
    var bheight = $("body").outerHeight();
    var bwidth = $("body").outerWidth();

    sscreen.height(bheight);
    sscreen.width(bwidth);
    sscreen.show();
  }
}

function infoMessage(elem) {
  var ibox = $("#superinfobox");
  ibox.append(elem);
  showSuperScreen();
}


   /**
    * To be called on a <li>. Sets up tagging input box with
    * autocompletion and refreshes the tagmenu.
    */
 function tagboxPrepare() {
     var lis = $(this);
     var tgbx = lis.find("input.tagbox");
     tgbx.autocomplete(document.folksonomie.getbase + "tagcomplete.php");

     var url = lis.resid();

     lis.find("a.tagbutton").click(
         function(event) {
           event.preventDefault();
           var meta = lis.find(".tagger select.metatagbox").val();

           if (tgbx.val()) {
             var cleanup = tagMenuCleanupFunc(lis, tgbx.val());
             $.ajax({
                      url: document.folksonomie.postbase + 'resource.php',
                      type: 'post',
                      datatype: 'text/text',
                      data: {
                        folksores: url,
                        folksotag: tgbx.val(),
                        folksometa: meta},
                      error: function(xhr, msg) {
                        if (xhr.status == 404) {
                          if (xhr.statusText.indexOf('ag does not exist') != -1) {
                            infoMessage(createTagMessage(tgbx.val(), url, meta, lis));
                          }
                          else {
                            alert("Erreur:  ressource non indexée. 404 "
                                  + xhr.statusText);
                          }
                        }
                        else {
                          alert('Erreur interne ' + xhr.statusText);
                        }
                      },
                      success: function (str) {
                        cleanup();
                        getTagMenu(
                          lis.find("div.emptytags"),
                          lis.attr("id").substring(3));
                        tgbx.val('');
                      }
                    });
           }
           else {
             alert('Il faut choisir un tag d\'abord');
           }
         });
 }

function tagMenuCleanupFunc(lis, tag) {
  return function() {
    lis.attr("class", "tagged");
    lis.find("inbox.tagbox").val('');
    currentTagsUpdate(tag, lis);
  };
}



/**
 * For the superscreen tag creation dialogue.
 */
function createTagMessage(tag, url, meta, lis, successfunc) {
  var tagFunk = tagResourceFunc(url, tag, meta, lis);
  var thediv = $("<div class='innerinfobox'></div>");

  var onSuccessFunc; //to be called on successful tag creation
  if (!successfunc) {
    onSuccessFunc = function () {
      tagFunk();
      thediv.append("<h2>Succès!</h2>");
      thediv.html("");  // reinitialize, otherwise messages accumulate
      $("#superscreen").hide();
      lis.find(".tagbox").val('');
      $("#grouptagbox").val('');
    };
  }
  else {
    onSuccessFunc = successfunc;
  }

  thediv.append($("<h3>Tag non trouvé</h3>"));
  thediv.append($("<p>Le tag <em>"
                  + tag +
                  "</em> n'est pas présent dans la base de données"
                  + "<p>"));
  thediv.append($("<p>Ajouter définitivement <em>"
                  + tag +
                  "</em> à la base ?</p>"));

  var yesbutton = $("<a class=\"yesno\" href=\"#\"></a>");
  yesbutton.html('Oui');
  yesbutton.click(
    function(event){
      event.preventDefault();
      $.ajax({
               url: document.folksonomie.postbase + 'tag.php',
               type: 'post',
               datatype: 'text/text',
               data: {
                 folksonewtag: tag
               },
               error: function(xhr, msg){
                 alert(xhr.statusText + " " + msg);
               },
               success: onSuccessFunc
             });
      });
    var lastpar = $("<p>");

  var nobutton = $("<a class=\"yesno\" href=\"#\">Non</a>").
    click(
      function(event){
        event.preventDefault();
        thediv.html(""); // reinitialize, otherwise messages accumulate
        $("#superscreen").hide();
      }
    );

  lastpar.append(yesbutton);
  lastpar.append(nobutton);
  thediv.append(lastpar);
  return thediv;
}

/**
 *  Creates a function closed over some useful variables.
 *
 */
function tagResourceFunc (url, tag, meta, lisarg) {
  var lis = lisarg;

  return function() {
    $.ajax({
           url: document.folksonomie.postbase + 'resource.php',
           type: 'post',
           datatype: 'text/text',
           data: {
             folksores: url,
             folksotag: tag,
             folksometa: meta
           },
           error: function(xhr, msg) {
             alert("Erreur : " + xhr.statusText);
           },
           success: function(data) {
             lis.addClass("tagged");
             lis.removeClass("nottagged");
             currentTagsUpdate(tag, lis);
             getTagMenu(lis, url);
           }});
  };
}

function currentTagsUpdate (tag, lis) {
  if (lis.find("span.currenttags").html().indexOf('"' + tag + '"') == -1)  {
    lis.find("span.currenttags").append(' "' + tag + '" ');
  }
}

function deleteButtonPrepare () {
  var lis = $(this);
  var button = lis.find("a.resdeletebutton");
  var resid = lis.attr("id").substring(3);

  button.click(
    function(event) {
      event.preventDefault();
      infoMessage(deleteResourceMessage(resid, lis));
    });
}

function deleteResourceMessage(resid, lis) {
  var thediv = $("<div class='innerinfobox'>");
  thediv.append($("<h3>Suppression définitive d'une resource</h3>"));
  thediv.append($("<p>Cette ressource sera effacée, ainsi que toutes"
                  + " ses associations avec des tags. La ressource ne "
                  + "sera plus réindexée. Cette action est définitive.</p>" ));

  thediv.append($("<p>Supprimer définitivement <em>\""
                  + lis.find("a.restitle").text()
                  + "\"</em> ?</p>"));

  var lastpar = $("<p>");
  var yesbutton = $("<a class=\"yesno\" href=\"#\">Oui</a>");
  yesbutton.click(
    function(event) {
      event.preventDefault();
      $.ajax({url: document.folksonomie.postbase + 'resource.php',
              type: 'post',
              datatype: 'text/text',
              data: {
                folksores: resid,
                folksodelete: 1
              },
              error: function(xhr, msg){
                alert(xhr.statusText);
                $("#superscreen").hide();
                thediv.html("");
              },
              success: function(data, msg){
                lis.hide();
                $("#superscreen").hide();
                thediv.html("");
              }
             });
    });
  lastpar.append(yesbutton);
  var nobutton = $("<a class=\"yesno\" href=\"#\">Non</a>").
    click(
      function(event){
        event.preventDefault();
        thediv.html("");
        $("superscreen").hide();
        } );
  lastpar.append(nobutton);
  thediv.append(lastpar);
  return thediv;
}

function noteEditBox(resid) {
  var edit = $("<div class='noteedit'>");
  edit.append(
    $("<textarea rows='10' cols='40' class='noteedit'>"));
  edit.append($("<a href='#' class='editbutton'>Enregistrer</a>"));
  edit.append($("<span> </span>")); // a lame span as separator
  edit.append($("<a href='#' class='editclose'>Annuler</a>"));

  edit.find("a.editbutton").click(
  function(event) {
    event.preventDefault();
    var text = $(this).siblings("textarea.noteedit").val();
    if (text.length > 0) {
      $.ajax({ url: document.folksonomie.postbase + 'resource.php',
               type: 'post',
               data: {
                 folksores: resid,
                 folksonote: text
               },
               error: function(xhr, msg){
                 alert(xhr.status + " " + xhr.statusText);
               },
               success: function(data, msg) {
                 var nc = edit.parent("li").find("span.notecount");
                 if (nc.text().length == 0) {
                   nc.append($("<a href='#' class=\"existingnotes\">1 note</a>"));
                   nc.find("a.existingnotes").click(
                     function(event){
                       event.preventDefault();
                       var lis = $(this).parent().parent("li");
                       var resid = lis.attr("id").substring(3);
                       lis.append(noteDisplayBox(resid));
                     });
                 }
                 else {
                   var nctext = nc.find("a").text();
                   var current = nctext.match(/\d+/);
                   var newtext = nctext.replace(current, Number(current) + 1);
                   nc.find("a").text(newtext);
                 }
                 edit.remove();
               }
             });
      }
  });

  edit.find("a.editclose").click(
    function(event){
      event.preventDefault();
      $(this).parent().remove();
    });
  return edit;
}

function noteDisplayBox(resid){
  var boxdiv = $("<div class='notedisplay'>");
  $.ajax({
           url: document.folksonomie.getbase + 'resource.php',
           type: 'get',
           datatype: 'text/xml',
           async: false,
           data: {
             folksores: resid,
             folksonote: 1
           },
           error: function(xhr, msg){
             alert(xhr.status + " " + xhr.statusText);
             boxdiv.append("<p>Aucune note</p>");
           },
           success: function(data){
             boxdiv.append(parseNotes(data));
           }
         });
  boxdiv.append($("<p class=\"buttonholder\"> "
                  + "<a href='#' class='closenotes'>"
                  + "Fermer</a> </p>"));
  boxdiv.find("a.closenotes").click(
    function(event){
      event.preventDefault();
      $(this).parent().parent("div").remove();
    });
  return boxdiv;
}

function parseNotes(data){
  var ul = $("<ul class='notelist'>");
  var parseFunc = parseNotesFunc(ul);
  $("note", data).each(parseFunc);
  return ul;
}

function parseNotesFunc(ul) {
  return function() {
      var item = $('<li>');
      item.attr("id", "note" + $(this).attr("noteid"));
      item.text($(this).text());
      item.append(deleteNoteButton($(this).attr("noteid")));
      ul.append(item);
  };
}

function deleteNoteButton(noteid) {
  var par = $("<p><a href=\"#\" class=\"deletenotebutton\">Supprimer</a></p>");
  par.find("a.deletenotebutton").click(
    function(event){
      event.preventDefault();
      $.ajax({
        url: document.folksonomie.postbase + 'resource.php',
        type: 'post',
        datatype: 'text/text',
        data: {
          folksonote: noteid,
          folksodelete: 1
        },
        error: function(xhr, msg){
          alert(xhr.status + " " + xhr.statusText);
        },
        success: function(data) {
          var existing =
              par.parent("li").parent("ul").parent("div")
                .parent("li").find("a.existingnotes");

          var before = existing.text().match(/\d+/);
          if (before == 1){
            existing.remove();
            }
          else if (before == 2) {
            existing.text('1 note');
          }
          else if (before > 2) {
            var after = before - 1;
            existing.text(after + ' notes');
          }
          else { // this should never happen...
            existing.text("notes");
          }
          par.parent("li").remove();
        }
        });
    });
  return par;
}



 /**
  * "tag" can be either a tagnorm or an id. Returns a correct tag url.
  */
 function tagUrl(tag) {
   return  webbase + "resourceview.php?tag=" + tag; // this is probably wrong!
 }

/** EAN13 stuff **/






/**
 * Prepare the suggestion <div> which will try to get meta information
 * about the page. This is what should happen when the user clicks on
 * the "Suggérer" button.
 *
 * "this" must be a div.suggestions
 */
function setupSuggestionDiv() {
  /* toggle find control */
  var div = $(this);
  div.find("a.getsuggestions").hide();
  div.find("a.closesuggest").show();

  var nodata_f = function() {
    div.append("<p>Erreur: l'url est incorrect ou indisponible.</p>");
  };

  var lis = div.resitem();
  var url = lis.find("a.resurl").attr("href");

  var success_f = function(data, status){
    buildSuggestions(div, data, status);
  };

  $.ajax({
           url: url,
           type: 'get',
           dataType: "html",
           success: success_f,
           error: nodata_f
         });
}

/**
 * to be called from setupSuggestionDiv() on success.
 */
function buildSuggestions(div, data, status) {
  if (! data.length) {
    return null;
  }
  var search_objs = [{ name: 'DC.Author',
                       reg: /<meta\s+name="DC.Author"\s+content="([^"]+)"/,
                       res: ''},
                     { name: 'ISBN',
                       reg: /<meta\s+scheme="ISBN"\s+content="([^"]+)"/,
                       res: ''},
                     { name: 'EAN13',
                       reg: /<meta\s+name="DC.Identifier"\s+scheme="ISBN"\scontent="([^"]+)"/,
                       res: ''}
                         ];
  var sul = $("<ul class='suggestions'>");
  for (var it = 0; it < search_objs.length; it++){
    var m = data.match(search_objs[it].reg);
    if (m){ //on successful match
      search_objs[it].res = m[1];
      sul.append("<li>" + search_objs[it].name + ": " + m[1] + "</li>");
        var lis = div.resitem();
      if ((search_objs[it].name == 'EAN13') &&
          (lis.find("input.ean13addbox").val() == '')) {
            lis.find("input.ean13addbox").val(search_objs[it].res);
            }
    }
  }

  div.append(sul);
  return div;
}

/** FOR RESOURCEVIEW **/

function editBox(resid, lis){
  var box = $("<div class='editbox'>");
  box.append($("<div class=\"tagger\">"
               + "<input type='text' class='tagbox' length='20'/>"
               + " <span class=\"infohead\">"
               + "Meta</span>"
               + "<select class=\"metatagbox\" size=\"1\">"
               + "</select>"
               + "<a class=\"tagbutton\" href=\"#\">Valider</a>"
               + "</div>"));
  return box;
}

/**
 * argument should be the actual select element where the options
 *  will be appended.
 */
function metaSelectOptions() {
  var selecto = $(this);
  if (document.folksonomie.hasOwnProperty("metaoptions")){
    for (var cnt = 0;
         cnt < document.folksonomie.metaoptions.length;
         cnt++){
      selecto.append(document.folksonomie.metaoptions[cnt].clone());
    }
  }
   else {
     document.folksonomie.metaoptions = new Array();

     var buildingFunc =
       function(){
         var opt = $("<option>");
         var fake = $("<option>fake</option>");
         opt.text($(this).text());
         document.folksonomie.metaoptions.push(opt);
         selecto.append(opt);
       };

     $.ajax({
              type: 'get',
              async: false,
              url: document.folksonomie.getbase + 'metatag.php',
              datatype: 'text/xml',
              data: {
                folksoall: 1
              },
              error: function(xhr, msg){
                alert("Metaglist " + xhr.status + " " + xhr.statusText);
              },
              success: function(xml, msg){
                $("metataglist meta", xml).each(buildingFunc);
              }
            });
   }
}

