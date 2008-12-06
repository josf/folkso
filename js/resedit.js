/**
 * document.folksonomie.postbase
 *
 * Relatve URI for all POST requests that, naturally, require
 * authentication.
 *
 * relative URI for GET requests that do not require authentication
 *
 * document.folksonomie.getbase = '';
 *
 * relative URI for building links towards various pages (display,
 * interface, etc.)
 */
var webbase = '';

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

    $("a.closeiframe").hide();
    $("#grouptagvalidate").click(
      function(event) {
        event.preventDefault();
        if ($("#grouptagbox").val()) {
          groupTag();
        }
      });
    $("#grouptagbox").autocomplete(document.folksonomie.getbase + "tagcomplete.php");

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
