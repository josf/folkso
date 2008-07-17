$(document).ready(function() {
  $("ul.editresources li").each(iframePrepare);
  $("ul.editresources li").each(tagboxPrepare);
  $("ul.editresources li").each(taglistHidePrepare);
//  $("ul.taglist li").each(tagremovePrepare);
});

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
    }
  );
  $(this).find("a.closeiframe").click(
    function(event) {
      event.preventDefault();
      holder.hide();
    }
  );
}

function tagboxPrepare() {
    var lis = $(this);
    var tgbx = lis.find("input.tagbox");
    tgbx.autocomplete("http://localhost/tagcomplete.php");

    var url = lis.find("a.resurl").attr("href");
    lis.find("a.tagbutton").click(
        function(event) {
            event.preventDefault();
            if (tgbx.val()) {
                 $.ajax({
                   url: 'http://localhost/resource.php',
                   type: 'post',
                   datatype: 'text/text',
                   data: {
                     folksores: url,
                     folksotag: tgbx.val()},
                   error: function(xhr, msg) {
                       alert(xhr.statusText + " " + xhr.responseText);
                     },
                   success: function (str) {
                     getTagMenu(
                       lis.find("div.emptytags"),
                       lis.attr("id").substring(3));
                   }
                 });
            }
          else {
          alert('Il faut choisir un tag d\'abord');
        }
    });
}

function tagremovePrepare() {
/**
 * To be called on a ul.tagmenu
 */

  var remove = $(this).find("a.remtag");

  var taglistdiv = $(this).parent();
  var resourceid = taglistdiv.parent().attr("id").substring(3);

  remove.click(function(event) {
        event.preventDefault();
        var tagid = $(this).siblings(".tagid").text();
        $.ajax({
            url: 'http://localhost/resource.php',
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

function getTagMenu(place, resid) {

    var dest = place;
    dest.find("ul.tagmenu").remove();

    $.ajax({ url: 'http://localhost/resource.php',
           type: 'get',
           datatype: 'text/xml',
           data: {
             folksores: resid,
             folksodatatype: 'text/xml'},
           success: function(xml) {
             var ul = $('<ul class="tagmenu">');
             $("taglist tag", xml).each(function() {
                                          var item = $('<li>');
                                          var taglink = $('<a>');
                                          taglink.attr("href", "beebop");
                                          taglink.append($(this).find('display').text() + ' ');
                                          var remlink = $('<a class="remtag" href="#">Désassocier</a>');
                                          item.append(taglink);

                                          /** add tag id **/
                                          item.append($("<span class='tagid'>"
                                                        + $(this).find('numid').text()
                                                        + "</span>"));

                                          item.append(remlink);
                                          ul.append(item);
                                        });
             dest.append(ul);
             $("ul.tagmenu").each(tagremovePrepare);
           },
           error: function(xhr, msg) {
             alert("An error here: " + msg);
           }});
}