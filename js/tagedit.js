var urlbase = '';

$.ajaxSetup({
              url: urlbase + 'tag.php',
              datatype: 'text/text'
              });

$(document).ready(function() {
                    $(".fusionbox").autocomplete(urlbase + "tagcomplete.php");
                    $('.tagentry').each(fkPrepare);

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
                          alert(newtag);
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

function fkPrepare(selector) {
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
        alert("you clicked?");
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

  var fusiontarget = lis.find("input.fusionbox").val();
  if (fusiontarget) {
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
             }
           });
    }
}

/**
 * Retreive the tag id on successful tag creation.
 */

function getTagId(data) {
  var tagid = data.match(/\d+/);
  return tagid[0];
}