//var urlbase = "http://www.fabula.org/commun3/folksonomie/";
var urlbase = "http://localhost/";

$(document).ready(
  function() {
    $("#showsql").click(
      function(event){
        event.preventDefault();
        $("#sql").show();
      });

    $("a.seedetails").click(
      function(event) {
        event.preventDefault();
        $(this).parent().siblings("div.details").show();
        $(this).siblings("a.hidedetails").show();
        $(this).hide();
      });

    $("a.hidedetails").click(
      function(event) {
        event.preventDefault();
        $(this).parent().siblings("div.details").hide();
        $(this).siblings("a.seedetails").show();
        $(this).hide();
      });

    $("ul.editresources li").each(iframePrepare);
    $("ul.editresources li").each(tagboxPrepare);
    $("ul.editresources li").each(taglistHidePrepare);

    $(".tagger .metatagbox").autocomplete(metatag_autocomplete_list);

    $("a.closeiframe").hide();
    $("#grouptagvalidate").click(
      function(event) {
        event.preventDefault();
        $("input.groupmod:checked").each(groupTag);
      });
    $("#grouptagbox").autocomplete(urlbase + "tagcomplete.php");
    //  $("ul.taglist li").each(tagremovePrepare);
  });

 /**
  * "tag" can be either a tagnorm or an id. Returns a correct tag url.
  */
 function tagUrl(tag) {
   return "http://localhost/tagview.php?tag=" + tag; // this is probably wrong!
 }

 /**
  *  "this" must be a list element from the main list of resources.
  */
 function iframePrepare() {
   var url = $(this).find("a.resurl").attr("href");
   var holder = $(this).find("div.iframeholder");
   $(this).find("a.openiframe").click(
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
//       $(this).parent().find("a.closeiframe").show();
       $(this).parent().parent().find("a.closeiframe").show();
     }
   );
   $(this).find("a.closeiframe").click(
     function(event) {
       event.preventDefault();
       holder.hide();
       $(this).hide();
       $(this).parent().parent().find("a.closeiframe").hide();
       $(this).parent().find("a.openiframe").show();
     }
   );
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

             var meta = '';
             if (lis.find("div.tagger input.metatagbox").val()) {
               meta = lis.find(".tagger input.metatagbox").val();
             }

             if (tgbx.val()) {
                  $.ajax({
                    url: urlbase + 'resource.php',
                    type: 'post',
                    datatype: 'text/text',
                   data: {
                      folksores: url,
                      folksotag: tgbx.val(),
                      folksometa: meta},
                    error: function(xhr, msg) {
                        alert("Autocomplete request failed: " +
                               xhr.statusText + " " + xhr.responseText);
                      },
                    success: function (str) {
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
     place.find("ul.tagmenu").remove();
     var tagMenuFromXmlFunction = tagMenuFunkMaker(place, resid);
    $.ajax({ url: urlbase + 'resource.php',
           type: 'get',
           datatype: 'text/xml',
           data: {
             folksores: resid,
             folksodatatype: 'xml'},
           success: tagMenuFromXmlFunction,
           error: function(xhr, msg) {
             alert("An error here: " + msg);
           }});
}

/**
 * Returns a function closed over "place", allowing us to get
 * to this variable when called without arguments in the $.ajax call.
 *
 *  Had to use this fancy closure system to get this to work.
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
    dest.append(ul);
    $("ul.tagmenu").each(tagremovePrepare);
  };
}


/**
 *  Parameter lis is a list item so that we know where to plug the
 *  new taglist back in when we are done.
 *
 * resource and tag can be either text or ids.
 */
function makeMetatagBox (resource, tag, lis) {
  var container = $("<span class='metatagbox'></span>")
    .append("<span class='infohead'>Modifier le metatag </class>");

  var box = $("<input type='text' class='metatagbox'>");
  box.autocomplete(metatag_autocomplete_list); //array defined in <script> on page.

  var button = $("<a href='#' class='metatagbutton'>metaValider</a>")
    .click(
      function(event){
        event.preventDefault();
        var newmeta = $(this).siblings("input").val();
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


function groupTag() {
  var lis = $(this).parent().parent("ul.editresources li");
  var url = lis.find("a.resurl").attr("href");
  var newtag = $("#grouptagbox").val();

  if (newtag) {
    $.ajax({
      url: urlbase + 'resource.php',
      type: 'post',
      datatype: 'text/text',
      data: {
        folksores: url,
        folksotag: newtag},
      error: function(xhr, msg) {
        alert(xhr.statusText + " " + xhr.responseText);
      }
    });
  }
}





