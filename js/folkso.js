//var urlbase = "http://fabula.org/commun3/folksonomie/";
var urlbase = "";

function modifyTagPrep(itemjq) {
  var thisid = itemjq.attr('id');
  var tagid = thisid.substring(5);

  var modform = itemjq.find("form.rename");
  var modbutton = modform.find("input[type=submit]");
  var modtextInput = modform.find("input[type=text]");
  modtextInput.val(itemjq.find("a.tagname").text());

  modbutton.click(
    function(event) {
      event.preventDefault();
      var newname = modtextInput.val();

      //validate the modification
      if ((newname == itemjq.find("a.tagname").text()) ||
        (modtextInput.val().trim == '')){
          alert("Pas de modification.");
          return;
      }
      //then make that change
      $.post(urlbase + 'tag.php',
             {folksotag: tagid,
              folksonewname: modtextInput.val()},
             function() {
               itemjq.find("a.tagname").text(newname);
             });

    });
}

function mergeTagPrep(itemjq) {
  var thisid = itemjq.attr('id');
  var tagid = thisid.substring(5);

  // text to put in textbox before user modification
  var emptymergeText = "Un autre tag";

  var mergeform = itemjq.find("form.merge");
  var mergetextInput = mergeform.find("input[type=text]");
  mergetextInput.val(emptymergeText);
  mergetextInput.focus(function() {
                        $(this).val('');
                      });

  mergeform.find("button[type=submit]").click(
    function(event) {
      event.preventDefault();
      var othertag = mergetextInput.val();

      if (othertag == emptymergeText) {
        alert("You must enter the name or id of the tag you wish to merge with.");
        return;
      }

      $.post(urlbase + 'tag.php',
             {folksotag: tagid,
              folksotarget: othertag},
                function () {
                  itemjq.hide();
                  itemjq.remove();
                });
    });
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
                            alert("404 but no tag " + xhr.statusText);
                          }
                        }
                        else {
                          alert('something else');
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
