/**
 * Part of "Folkso"
 *  author: Joseph Fahey
 *  copyright 2008 Gnu Public Licence
 */


if (! document.hasOwnProperty("folksonomie")) {
  document.folksonomie = new Object();
}
var posttagphp = document.folksonomie.postbase + 'tag.php';
$.ajaxSetup({
              datatype: 'text/text'
              });

$(document).ready(function() {

                    $("a.selectletter").click(
                      function(event){
                        event.preventDefault();
                        var letter = $(this).attr("id").substr(6, 1);
                        var lis = $(this).parent();

                        /** list already present but hidden? **/
                        if (lis.find("ul.taglist").length > 0) {
                          var ultl = lis.find("ul.taglist");
                          if (ultl.css("display") == "block"){
                            ultl.hide();
                          }
                          else {
                            ultl.show();
                          }
                        }
                        else { /** get list **/
                          $.ajax({
                                 url: document.folksonomie.getbase + 'tag.php',
                                 type: 'get',
                                 data: {
                                   folksobyalpha: letter
                                 },
                                 datatype: 'text/xml',
                                 error: function(xhr, msg) {
                                   alert(msg);
                                 },
                                 success: function(xml){
                                   var ul = $("<ul class=\"taglist\">");
                                   var lisfunc = function(li) {
                                     ul.append(li);
                                   };
                                   $("taglist tag", xml).each(
                                     function() {
                                       var it = makeTageditListitem(
                                         $(this).find("numid").text(),
                                         $(this).find("display").text(),
                                         $(this).find("popularity").text()
                                       );
                                       lisfunc(it);
                                     });
//                                     lis.each(fkPrepare); // already done in activeEdit()
                                     lis.append(ul);
                                     lis.find("ul.taglist li").each(
                                       function(){
                                         $(this).each(activateEdit);
                                         $(this).each(activateFusionCheck);
                                     });
                                 }
                               });
                          }
                      });

                    $("a.restags").click(
                      function(event){
                        event.preventDefault();
                        $("li.res").show();
                        $("li.nores").hide();
                      });
                    $("a.norestags").click(
                      function(event){
                        event.preventDefault();
                        $("li.nores").show();
                        $("li.res").hide();
                      });
                    $("a.seealltags").click(
                      function(event){
                        event.preventDefault();
                        $("li.res").show();
                        $("li.nores").show();
                      });
                    $("#tagcreatebutton").click(
                      function(event) {
                        event.preventDefault();

                        if( $("#tagcreatebox").val()) {
                          var newtag = $("#tagcreatebox").val();
                          $.ajax({
                                   url: posttagphp,
                                   type: 'post',
                                   datatype: 'text/text',
                                   data: {
                                     folksonewtag: newtag
                                   },
                                   error: function(xhr, msg) {
                                     alert("Echec: " + xhr.statusText);
                                   },
                                   success: function(data, status) {
                                     var tagid = getTagId(data);
                                     var clone = $("ul.taglist li:first").clone();
                                     clone.attr("id", 'tagid' + tagid);
                                     clone.find("a.tagname").text(newtag);
                                     clone.find("a.edit").click(
                                       function(event) {
                                         event.preventDefault();
                                         // first parent is a <p>
                                         $(this).parent().parent("li").find(".tagcommands").show();
                                         $(this).hide();
                                       });
                                     clone.find("input.renamebox").attr("value", newtag);
                                     clone.find("span.tagpopularity").text(" (0 ressources) ");
                                     clone.each(fkPrepare);
                                     $("ul.taglist").prepend(clone);
                                     $("#tagcreatebox").val('');
                                     clone.find("a.closeeditbox").click(
                                       function(event){
                                         event.preventDefault();
                                         $(this).parent().parent(".tagcommands").hide();
                                         $(this).parent().parent().parent("li").find("a.edit").show();
                                       });
                                   }
                          });
                        }
                      });
                  });

function fkPrepare() {
  // tagid is 'tagid' + number
  var lis = $(this);
  var thisid = lis.attr('id');
  var tagid = thisid.substring(5);

  lis.find("button.delete").click(
    function(event) {
      event.preventDefault();
      $.post(posttagphp,
             {folksotag: tagid,
              folksodelete: '1'},
             function() {
               $("#" + thisid).hide();
               $("#" + thisid).remove();
             });
    });

    lis.find("input.renamebutton").click(
      function(event) {
        event.preventDefault();
        var newname = lis.find("input.renamebox").val();
        if (newname &&
            (newname != lis.find("a.tagname").text())) {
          var updatefunc =
            function() {
              lis.find("a.tagname").text(newname);
            };
          $.ajax({
                   url: posttagphp,
                   type: 'post',
                   data: {
                     folksotag: tagid,
                     folksonewname: newname
                   },
                   success: function(data, str) {
                     lis.find("a.tagname").text(newname);
                   },
                   error: function(xhr, msg) {
                     alert("Echec: " + xhr.statusText);
                   }
                 });
          }
        });

  lis.find(".fusionbutton").click(
    function(event){
      event.preventDefault();
      var oldpopularity = getPopularity(lis);
      var fusiontarget = lis.find("input.fusionbox").val();
      if (fusiontarget){
        if (fusiontarget == lis.find("a.tagname").text()) {
          alert("On ne peut pas fusionner un tag avec le même tag");
        }
        else {
                alert("fusion");

          $.ajax({
                   url: posttagphp,
                   type: 'post',
                   data: {
                     folksotag: tagid,
                     folksotarget: fusiontarget
                   },
                   success: function(data, str){
                     lis.remove();
                   },
                   error: function(xhr, msg){
                     alert("Echec: " + xhr.statusText);
                   },
                   complete: function(xhr){
                     if (xhr.status == '204'){
                       var targetid = xhr.getResponseHeader('X-Folksonomie-TargetId');
                       var targetpop = getPopularity($("#tagid" + targetid));
                       var newpop = Number(oldpopularity) + Number(targetpop);
                       $("#tagid" + targetid).find("span.tagpopularity")
                         .text(" (" + newpop + " ressources) ");
                     }
                   }
                 });
        }
      }
    });
}
/**
 * Retreive the tag id on successful tag creation.
 */

function getTagId(data) {
  var tagid = data.match(/\d+/);
  return tagid[0];
}

function getPopularity(lis) {
  var matches = lis.find("span.tagpopularity").text().match(/\d+/);
  return matches[0];
}

function makeMfusionFunc(targ) {
  return function() {
  var lis = $(this).parent().parent("li");
  var thistag = lis.attr("id").substring(5);
  $.ajax({
    type: 'post',
    url: posttagphp,
    data: {
      folksotag: thistag,
      folksotarget: targ
    },
    success: function(data, str) {
      lis.remove();
      /* clear preview text */
      document.folksonomie.currentEdit.find("p.multifusionvictims").text("");
    },
    error: function(xhr, msg){
      alert("Echec: la fusion du tag "
            + lis.find("a.tagname").text()
            + " a échoué. "
            + xhr.status + " "
            + xhr.statusText + " target " + targ + " source " + thistag);
      }
    });
  };
}

/**
 * Find any checked multifusion boxes and return a
 * string with all the tagnames.
 */
function getMVictims() {
  var str = ''; //return string

  $("input.fusioncheck:checked").each(
    function() {
      var tagname =$(this).parent().parent("li").find("a.tagname").text();
      str = str + ' "' + tagname + '" ';
    }
  );
  return str;
}


function addtoPreview(tag) {
  var preview =
      document.folksonomie.currentEdit.find("p.multifusionvictims");
  var text = preview.text();
  if (text.indexOf('"' + tag + '"') == -1) { // ie. not found
    preview.text( text + ' "' + tag + '" ');
  }
}

function removefromPreview(tag) {
  var preview =
    document.folksonomie.currentEdit.find("p.multifusionvictims");
  var text = preview.text();
  var quotedTag = '"' + tag + '"';
  if (text.indexOf(quotedTag) > -1) {
    preview.text(text.replace(quotedTag, ''));
  }
}

/** now to be  called on a <li> **/
function activateFusionCheck() {
  var cbox = $(this).find("input.fusioncheck");

  cbox.attr("disabled", "disabled");
  cbox.attr("checked", false);
  cbox.change(
    function(event){
      var checkedTag =
        $(this).parent().parent("li").find("a.tagname").text();
      if ($(this).attr('checked') == true){
        addtoPreview(checkedTag);
      }
      else {
        removefromPreview(checkedTag);
      }
    });
}

function activateEdit() {
  $(this).find("a.edit").click(
    function(event) {
      event.preventDefault();
      // first parent is a <p>
      var lis = $(this).parent().parent("li");
      $("div.tagcommands").hide(); // hide all others first
      lis.each(fkPrepare);
      lis.find(".fusionbox").autocomplete(document.folksonomie.getbase
                                   + "tagcomplete.php");

      document.folksonomie.currentEdit = lis;
      lis.find("div.tagcommands").show();
      lis.find("input").show();
      $(this).hide();
      $("input.fusioncheck").attr('disabled', '');
      $(this).siblings("input.fusioncheck")
        .attr('disabled', 'disabled');
      $(this).siblings("input.fusioncheck")
        .attr('checked', false);

      var targtag = lis.attr("id").substring(5);
      var mfusionfunc = makeMfusionFunc(targtag);
      lis.find("a.multifusionbutton").click(
        function(event) {
          event.preventDefault();
          $("input.fusioncheck:checked").each(mfusionfunc);
        });

      lis.find('a.closeeditbox').click(
        function(event){
          event.preventDefault();
          document.folksonomie.
            currentEdit.find("p.multifusionvictims").text("");
          document.folksonomie.currentEdit = '';
          $(this).parent().parent(".tagcommands").hide();
          $("input.fusioncheck").attr('checked', '');
          $("input.fusioncheck").attr('disabled', 'disabled');
          $(this).parent().parent().parent("li").find("a.edit").show();
        });
    });
}


function makeTageditListitem (id, display, popularity) {
  var item = $("<li class=\"tagentry nores\">");
  item.attr("id", "tagid" + id);

  /* paragraph 1: name and checkbox */
  var p1 = $("<p>");
  p1.append($("<input type=\"checkbox\" "
              + "class=\"fusioncheck\" "
              + "disabled=\"disabled\"/>"));

  var taglink = $("<a class=\"tagname\">");
  taglink.attr("href", "resourceview.php?tag=" + id);
  taglink.text(display);
  p1.append(taglink);

  var spanpop = $("<span class=\"tagpopularity\">");
  spanpop.text(" (" + popularity + " ressources)");
  p1.append(spanpop);

  p1.append($("<a href='#' class='edit'> Editer </a>"));
  item.append(p1);

  /** div tagcommands **/
  var divtc = $("<div class=\"tagcommands\">");
  var renameform =
    $('<form class="rename" action="tag.php" method="post">');
  var renamep =
    $("<p>Modifier : </p>");
  renamep.append($("<input "
                   + "class=\"renamebox\" type=\"text\" maxlength=\"255\" "
                   + "size=\"20\" name=\"folksonewname\" value=\""
                   + display + "\">"));
  renamep.append($("<input type=\"submit\" value=\"Modifier\" "
                   + "class=\"renamebutton\">"));
  renameform.append(renamep);
  divtc.append(renameform);

  /** delete **/
  var deleteform =
    $("<form class=\"delete\" action=\"tag.php\" method=\"post\">");
  deleteform.append(
    $("<p>Supprimer : "
      + "<button class=\"delete\" type=\"submit\" "
      + "name=\"folksotag\" value=\"" + id + "\">"
      + "Suppression"
      + "</button>"
      + "</p>"));
  divtc.append(deleteform);

  /** merge **/
  var mergeform =
    $("<form class=\"merge\" action=\"tag.php\" method=\"post\">");
  var mergep =
    $("<p>Fusionner avec (le tag " + display + " sera supprimé) :</p>");
  mergep.append("<input class=\"fusionbox\" name=\"folksotarget\" "
                + "type= \"text\" maxlength=\"255\" size=\"20\">");
  mergep.append("<button type=\"submit\" value=\"" + id + "\""
                +  " class=\"fusionbutton\" name=\"folksotag\">"
                + "Fusionner"
                + "</button> ");
  mergeform.append(mergep);
  divtc.append(mergeform);

  /** multifusion **/
  var divmf
    = $("<div class=\"multifusion\"><h4>Fusion Multiple</h4></div>");
  divmf.append(
    $("<p>Sélectionner sur la page les tags à fusionner avec"
      + " <em>" + display + "</em>. Les autres tags seront supprim&#xE9;s "
      + "au profit de celui-ci.")
    );
  divmf.append(
    $("<p class=\"multifusionvictims\">")
  );
  divmf.append(
    $("<p><a class=\"multifusionbutton\" href=\"#\">Multi-fusion</a></p>")
  );
  divtc.append(divmf);
  divtc.append($("<p><a href=\"#\" class=\"closeeditbox\">Fermer</a>"));
  item.append(divtc);

  return item;
}