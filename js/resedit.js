//var urlbase = "/commun3/folksonomie/";
var urlbase = "";

$(document).ready(
  function() {
    $("#showsql").click(
      function(event){
        event.preventDefault();
        $("#sql").show();
      });

    $("ul.editresources li").each(iframePrepare);
    $("ul.editresources li").each(tagboxPrepare);
    $("ul.editresources li").each(taglistHidePrepare);
    $("ul.editresources li").each(deleteButtonPrepare);

//    $(".tagger .metatagbox").autocomplete(metatag_autocomplete_list);
//    $("#groupmetatagbox").autocomplete(metatag_autocomplete_list);

    $("a.closeiframe").hide();
    $("#grouptagvalidate").click(
      function(event) {
        event.preventDefault();
        if ($("#grouptagbox").val()) {
          groupTag();
        }
      });
    $("#grouptagbox").autocomplete(urlbase + "tagcomplete.php");
    //  $("ul.taglist li").each(tagremovePrepare);

    $("#cleargroupchecks").click(
      function(event) {
        event.preventDefault();
        $("input.groupmod:checked").attr("checked", "");
      });

    $("#groupchecksall").click(
      function(event) {
        event.preventDefault();
        $("input.groupmod").attr("checked", "checked");
      });

    $("a.existingnotes").click(
      function(event){
        event.preventDefault();
        var lis = $(this).parent().parent("li");
        var resid = lis.attr("id").substring(3);
        lis.append(noteDisplayBox(resid));
      });


    $("a.addnote").click(
      function(event){
        event.preventDefault();
        var lis = $(this).parent("li");
        var resid = lis.attr("id").substring(3);
        lis.append(noteEditBox(resid));
      });

    // for debugging
    $("#ss").click(
      function(event){
        event.preventDefault();
        showSuperScreen();
      }
    );

    $("#closess").click(
      function(event) {
        event.preventDefault();
        $("#superscreen").hide();
      });
  });

 /**
  * "tag" can be either a tagnorm or an id. Returns a correct tag url.
  */
 function tagUrl(tag) {
   return  urlbase + "tagview.php?tag=" + tag; // this is probably wrong!
 }

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
       var ifr = document.createElement("iframe");
       ifr.src = url;
       ifr.className = "preview";

       if (holder.children().is("iframe")) {
         holder.show();
       }
       else {
         holder.append(ifr);
       }
       $(this).hide();
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
   var resourceid = taglistdiv.parent().parent().attr("id").substring(3);

   remove.click(function(event) {
         event.preventDefault();
         var tagid = $(this).siblings(".tagid").text();
         $.ajax({
             url: urlbase + 'resource.php',
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
 * To be called on a list element.
 */
 function taglistHidePrepare() {
   var resourceid = $(this).attr("id").substring(3);
   var lis = $(this);

   $(this).find("a.seetags").click(
     function(event) {
       event.preventDefault();
       getTagMenu(lis.find("div.emptytags"), resourceid);
     }
   );

   $(this).find("a.hidetags").click(
     function(event) {
       event.preventDefault();
       lis.find("ul.tagmenu").remove();
       }
   );
 }

 /**
  * Create or update a tag menu. (Most of the work is done
  * by tagMenuFromXml.)
  */
 function getTagMenu(place, resid) {
//     place.find("ul.tagmenu li").hide();
     var tagMenuFromXmlFunction = tagMenuFunkMaker(place, resid);
    $.ajax({ url: urlbase + 'resource.php',
           type: 'get',
           datatype: 'text/xml',
           data: {
             folksores: resid,
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
        var item = $('<li>');
        var taglink = $('<a>');
        taglink.attr("href", "beebop");
        taglink.attr("class", "tagdisplay");
        taglink.append($(this).find('display').text() + ' ');
        item.append(taglink);
        /** add tag id **/
        item.append($("<span class='tagid'>"
                      + $(this).find('numid').text()
                      + "</span>"));
        item.append($('<a class="remtag" href="#">Désassocier</a>'));
        /** meta tag (if not "normal") **/
        if ($(this).find('metatag').text() != 'normal') {
          item.append($("<span class='meta'> Relation: "
                        + $(this).find('metatag').text()
                        + "</span>"));
        }

        item.append(makeMetatagBox(resid, //closure
                                   $(this).find('numid').text(),
                                   place));
        ul.append(item);
      });

    /** Now that we have our new list, put it back into the DOM **/
    if (dest.find("ul.tagmenu").length) {
      dest.find("ul.tagmenu").replaceWith(ul);
    }
    else {
      dest.append(ul);
    }
    dest.find("ul.tagmenu").each(tagremovePrepare);
  };
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
  for (var i = 0; i < metatag_autocomplete_list.length; i++) {
    box.append("<option>" + metatag_autocomplete_list[i] + "</option>");
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
  var container = $("<span class='metatagbox'></span>")
    .append("<span class='infohead'>Modifier le metatag </span>");
//  box.autocomplete(metatag_autocomplete_list); //array defined in <script> on page.

  var box = metatagDropdown(metatag_autocomplete_list, "metatagbox");
  var button = $("<a href='#' class='metatagbutton'>Ajouter métatag</a>")
    .click(
      function(event){
        event.preventDefault();
        var newmeta = $(this).siblings("select").val();
        $.ajax({
                 url: urlbase + 'resource.php',
                 type: 'post',
                 data: {
                   folksores: resource,
                   folksotag: tag,
                   folksometa: newmeta
                 },
                 error: function(xhr, msg) {
                   alert(msg);
                 },
                 success: function(data) {
                   getTagMenu(lis, resource);
                 }
               });
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
           url: urlbase + 'resource.php',
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
           url: urlbase + 'resource.php',
           type: 'post',
           datatype: 'text/text',
           data: {
             folksores: firstres,
             folksotag: newtag,
             folksometa: meta
           },
           error: function(xhr, msg) {
             if (xhr.status == 404) {
               if (xhr.statusText.indexOf('ag does not exist') != -1) {
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
               url: urlbase + 'resource.php',
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
     tgbx.autocomplete(urlbase + "tagcomplete.php");

     var url = lis.find("a.resurl").attr("href");

     lis.find("a.tagbutton").click(
         function(event) {
           event.preventDefault();
         var meta = lis.find(".tagger select.metatagbox").val();

           if (tgbx.val()) {
             var cleanup = tagMenuCleanupFunc(lis, tgbx.val());
             $.ajax({
                      url: urlbase + 'resource.php',
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
               url: urlbase + 'tag.php',
               type: 'post',
               datatype: 'text/text',
               data: {
                 folksonewtag: tag
               },
               error: function(xhr, msg){
                 alert(xhr.statusText);
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
           url: urlbase + 'resource.php',
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
      $.ajax({url: urlbase + 'resource.php',
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
      $.ajax({ url: urlbase + 'resource.php',
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
           url: urlbase + 'resource.php',
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
        url: urlbase + 'resource.php',
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
