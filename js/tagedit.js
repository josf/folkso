var urlbase = '';

$.ajaxSetup({
              url: urlbase + 'tag.php',
              datatype: 'text/text'
              });

$(document).ready(function() {
                    $(".fusionbox").autocomplete(urlbase + "tagcomplete.php");
                    $('li.tagentry').each(fkPrepare);

                    $('a.edit').click(
                      function(event) {
                        event.preventDefault();
                        // first parent is a <p>
                        $(this).parent().parent("li").find(".tagcommands").show();
                        $(this).hide();
                      });
                    $('a.closeeditbox').click(
                      function(event){
                        event.preventDefault();
                        $(this).parent().parent(".tagcommands").hide();
                        $(this).parent().parent("li").find("a.edit").show();
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
                                   url: urlbase + 'tag.php',
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
                                     clone.attr("id", 'tag' + tagid);
                                     clone.find("a.tagname").text(newtag);
                                     clone.find("a.edit").click(
                                       function(event) {
                                         event.preventDefault();
                                         // first parent is a <p>
                                         $(this).parent().parent("li").find(".tagcommands").show();
                                         $(this).hide();
                                       });
                                     clone.find("input.renamebox").text(newtag);
                                     $("ul.taglist").prepend(clone);
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
      $.post(urlbase + 'tag.php',
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
          $.ajax({
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
        $.ajax({
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