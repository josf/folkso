if (! document.hasOwnProperty("folksonomie")) {
  document.folksonomie = new Object();
}

var getresphp = document.folksonomie.getbase + 'resource.php';
var postresphp = document.folksonomie.postbase + 'resource.php';
var webbase = '';

$(document).ready(
  function(){
    $("a.editresource").click(
      function(event) {
        event.preventDefault();
        $(this).parent().siblings("div.details").show();
        $(this).hide();
        $(this).siblings("a.closeedit").show();
      });

    $("a.closeedit").click(
      function(event){
        event.preventDefault();
        $(this).parent().siblings("div.details").hide();
        $(this).hide();
        $(this).siblings("a.closeedit").show();
      });

    $("ul.resourcelist li").each(iframePrepare);
    $("ul.resourcelist li").each(tagboxPrepare);
    $("ul.resourcelist li").each(taglistHidePrepare);
    $("ul.resourcelist li").each(deleteButtonPrepare);
    $("select.metatagbox").each(metaSelectOptions);

  });
