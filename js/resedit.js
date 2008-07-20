var urlbase = "http://fabula.org/commun3/folksonomie/";

$(document).ready(function() {
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
  $("a.closeiframe").hide();
  $("#grouptagvalidate").click(
    function(event) {
      event.preventDefault();
    $("input.groupmod:checked").each(groupTag);
    });
  $("#grouptagbox").autocomplete(urlbase + "tagcomplete.php");
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
      $(this).hide();
      $(this).parent().find("a.closeiframe").show();
    }
  );
  $(this).find("a.closeiframe").click(
    function(event) {
      event.preventDefault();
      holder.hide();
      $(this).hide();
      $(this).parent().find("a.openiframe").show();
    }
  );
}

function tagboxPrepare() {
  /**
   * To be called on a <li>. Sets up tagging input box with
   * autocompletion and refreshes the tagmenu.
   */
    var lis = $(this);
    var tgbx = lis.find("input.tagbox");
    tgbx.autocomplete(urlbase + "tagcomplete.php");

    var url = lis.find("a.resurl").attr("href");
    lis.find("a.tagbutton").click(
        function(event) {
            event.preventDefault();
            if (tgbx.val()) {
                 $.ajax({
                   url: urlbase + 'resource.php',
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
                     tgbx.val('');
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

    $.ajax({ url: urlbase + 'resource.php',
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





