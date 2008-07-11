$(document).ready(function() {
  $("ul.editresources li").each(iframePrepare);
  $("ul.editresources li").each(tagboxPrepare);
  $("ul.editresources li").each(taglistHidePrepare);
  $("ul.taglist li").each(tagremovePrepare);
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
  /** This does not need to be a separate function. (We could just
  write $(".tagbox").autocomplete(...) but we might want to add  some
  arguments, to exclude tags already present for exampel. So  for now
  I am going to leave this here. */

  var tgbx = $(this).find("input.tagbox");
  tgbx.autocomplete("http://localhost/tagcomplete.php");

  var url = $(this).find("a.resurl").attr("href");
  $(this).find("a.tagbutton").click(
    function(event) {
      event.preventDefault();
      if (tgbx.val()) {
        var xhr = $.ajax({
                           url: 'http://localhost/resource.php',
                           type: 'post',
                           datatype: 'text/text',
                           data: {
                             folksores: url,
                             folksotag: tgbx.val()},
                           error: function (xhr, msg) {
                             alert(msg);
                           },
                           success: function (str) {
                             alert(str);
                           }
                         });
        }
        else {
          alert('Il faut choisir un tag d\'abord');
        }
    });
}



function tagremovePrepare() {

  var tagid = $(this).find("span.tagid").text();

  var remove = $(this).find("a.removetag");
  var taglist = $(this).parent();
  var resourceid = taglist.parent().attr("id").substring(3);

}

function taglistHidePrepare() {
  var taglist = $(this).find("ul.taglist");
  $(this).find("a.seetags").click(
    function(event) {
      event.preventDefault();
      taglist.show();
    }
  );
  $(this).find("a.hidetags").click(
    function(event) {
      event.preventDefault();
      taglist.hide();
    }
  );
}