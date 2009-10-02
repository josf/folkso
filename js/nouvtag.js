/** Find nouvelles to tag: find all nouvelles,
 * eliminate non valid ones, get resource id **/

var fk = fk || {};

fk.getresurl = '/resource.php';
fk.postresurl= '/resource.php';
fk.tag_auto_url = "/tagcomplete.php";
fk.metatag_auto_url = "/metatag.php";
fk.meta_list_url = "/metatag.php";

fk.posttagurl = '/tag.php';
fk.gettagurl = '/tag.php';

fk.image_path = '/';

fk.metatags = [];

fk.row_filter = function(row) {
  var tr =$(row);
  if (tr.find("td").length != 4) {
    return false;
  }
    if($(tr.find("td")[3]).text().search(/valid/) != -1) {
    return false;
  }
  return true;
};


 /** Activate click mechanism
  *
 * The idea here it to do as little as possible until the user clicks
 * on the tagging link. Only then do we gather up all the relevant
 * information and set up the tagging environment.
 *
 * @param tr $("tr")
 *
  **/
fk.prep_tagbutton = function (tr){
  var titre_col = $(tr.find("td")[2]);
  var action_col = $(tr.find("td")[3]);

  var title = titre_col.find("a").text();
  var url = titre_col.find("a").attr("href");

  console.log("found " + title + " and " + url);
  var box = fk.tagbox_setup(title, url);

  action_col.append(fk.initialize_tagbox(box, url, title));
};

/**
 * Build the initial html. No actions included yet.
 *
 * @returns jQuery object
 */
fk.tagbox_setup = function (title, url) {
  return $(
    "<div class=\"tagbox\">"
    + "<p class=\"tag_title\">Tagger: <em>"
    + title
    + "</em></p>"
    + "<p>"
    + url
    + "</p>"
    + "<p class=\"curr_tags\"></p>"
    + "<div class=\"tag_action\">"

    + "<p class=\"tagcontrols\">Tag: "
    + "<input type=\"text\" class=\"maintag\" length=\"50\"></input> "
    + "</p>"
    + "<p class=\"tagcontrols\">Métatag: "
    + "<select class=\"meta\"></select>"
    + "</p>"

    + "</div>"
    + "<p class=\"tag_dialogue\"></p> "
    + "<table>"
    + "<tr>"
    + "<td><img src=\"/validate_button.png\" class=\"validate_taggage\" "
    + "alt=\"Effectuer le taggage\">"
    + "</img></td>"
    + "<td><img src=\"" + fk.image_path + "close_button.png\" "
    + " class=\"close_box\" alt=\"Fermer\"></img></td></tr>"
    + "</div>"
  );
};

/**
 * Get tags for the given resource and insert them into the DOM.
 *
 * @param res String resource url
 * @param insert $() jQuery DOM object. The place where the resulting
 * tag string will be appended.
 * @param tag_fmt Function to be called on each xml tag result
 */
fk.insert_current_tags = function(res, insert, tag_fmt){
  $.ajax({
           url: fk.getresurl,
           type: 'get',
           datatype: 'xml',
           data: {
             folksores: res,
             folksodatatype: 'xml'
           },
           error: function(xhr, msg){
             console.log(xhr.status + " " + msg + " " + xhr.statusText);
           },
           success: function(answer){
             var xmltags = $(answer).find("tag");
             for (var i = 0; i < xmltags.length; ++i){
               insert.append(
                 tag_fmt(
                   res,
                   $(xmltags[i]).find("display").text(),
                   $(xmltags[i]).find("numid").text(),
                   $(xmltags[i]).find("metatag").text()
                 )
               );
             }
           }
         });
};


fk.make_currtag_unit = function (res, tagdisp, tagid, meta){
  var metapart = "";
  if ((meta.length) &&
      meta != "normal") {
        metapart = "(" + meta + ")";
  }

  var h = $("<span class=\"a_tag\">"
           + tagdisp
           + metapart
           + "<a class=\"tag_del_button\" href=\"#\">"
           + "<span class=\"tag_del_button_text\">Suppr.</span>"
           + "</a> "
           + "</span> ");

  h.find("a").click(
    function(ev){
      ev.preventDefault();
      $.ajax({
               url: fk.postresurl,
               type: 'post',
               datatype: 'text/text',
               data: {
                 folksores: res,
                 folksotag: tagid,
                 folksodelete: 1
               },
               error: function(xhr, msg){
                 console.log(xhr.status + " " + msg);
               },
               success: function (data){
                 h.remove();
               }
      });
    }
  );
  return h;
};

/**
 * Inserts a "data" object into another object, passing the "data"
 * object by value and not by reference. This assumes that the "data"
 * object does not contain any objects itself, just string values.
 *
 * @param data Object the data
 * @param bigger Object Where we are inserting Data
 * @return Object (Still a reference to bigger)
 */
fk.integrate_ajax_data = function(data, bigger){
  bigger.data = {};
  for (var k in data){
    bigger.data[k] = data[k];
  }
  return bigger;
};

fk.tagCreateDialogue = function(ajob, box){
  var dialogue =  box.find(".tag_dialogue");
  dialogue.html(
    "Le tag"
    + "<em> " + ajob.data.folksotag + "</em> n'existe pas. "
    + "Faut-il le créer?<br/>"
    + "<span class=\"button yes_create\">Créer</span> "
    + "<span class=\"button no_create\">Annuler</span> "
  );

  dialogue.find("span.yes_create").click(
    function (){ // look ma! no ev!

      $.ajax({
               url: fk.posttagurl,
               type: 'post',
               datatype: 'text/text',
               data: {
                 folksonewtag: ajob.data.folksotag
               },
               error: function(xhr, msg) {
                 alert("Error: " + xhr.status + " "
                       + xhr.statusText + " " + msg);
               },
             success: function(answer){
               $.ajax(ajob);
               dialogue.html("Le tag a été créé.");
             }
             });
    });

  dialogue.find("span.no_create").click(
    function(){
      box.closest(".tagbox").find("input.maintag").val("");
      dialogue.html("");
    });

};

/**
 * @param box jQuery tagbox elem
 */
fk.initialize_tagbox = function(box, url, title){
  /**  Fermer **/
  box.find("img.close_box").click(
    function(ev){
      ev.preventDefault();
      box.remove();
    });

  var tagput = box.find("input.maintag");
  var metaput = box.find("select");

  /** immediately report resource to system (might be first time)**/
  $.ajax({
           url: fk.postresurl,
           type: 'post',
           datatype: 'text/text',
           data: {
             folksores: url,
             folksonewresource: 1,
             folksonewtitle: title
           },
           error: function(xhr, msg){
             alert(xhr.statusText + xhr.status);
           }
         });

  fk.insert_current_tags(url,
                         box.find("p.curr_tags"),
                         fk.make_currtag_unit);

  /** setup autocomplete (note that we have to modify jquery.autocomplete
   * to add "folkso" in front of "q" header)
   **/
  tagput.autocomplete(fk.tag_auto_url);

  var sel = box.find("select");
  sel.append("<option></option>");
  if (fk.metatags.length > 0){

    for (var i = 0; i < fk.metatags.length; ++i){
      sel.append("<option>" + fk.metatags[i] + "</option>");
    }
  }


  box.find("img.validate_taggage").click(
    function(ev){
      ev.preventDefault();
      var tag_text = jQuery.trim(tagput.val());
      if (tag_text.length > 1){
        var ajax_base = {
                 url: fk.postresurl,
                 type: 'post',
                 datatype: 'text/text'
          };
        var ajax_data = {
                   folksores: url,
                   folksotag: tag_text,
                   folksometa: metaput.val()
        };

        var ajax_req1 = fk.integrate_ajax_data(ajax_data, ajax_base);
        ajax_req1.error = function(xhr, msg){
          if (xhr.statusText.indexOf('ag does not exist') != -1){
            fk.tagCreateDialogue(
              fk.integrate_ajax_data(ajax_data, ajax_base),
              box
            );
          }
        };

        ajax_req1.success = function(){
                   console.log("success with " + tagput.val());
                   box.find("p.curr_tags")
                     .append(
                       fk.make_currtag_unit(url,
                                            tag_text,
                                            "",
                                            metaput.val())
                     );
                   tagput.val("");
        };

        $.ajax(ajax_req1);
      }
    });


  return box;
};


$(document).ready(
  function(){

    var rows = jQuery.grep($("tr"),
                           fk.row_filter
                          );
    console.log("found " + rows.length + " valid rows");

    /** slice is to avoid 1st row (headers) **/
    $(rows.slice(1)).each(function() {
                            var rw = this;
                            fk.insert_taglink.call(rw, fk.prep_tagbutton);
                          });


    $.get(fk.meta_list_url,
          {folksoall: 1},
          function(data){
            var ret = "";
            console.log(data);
            console.log($(data).find("meta").length);
            var mets = $(data).find("meta");
            console.log("mets length " + mets.length);
            console.log("mets type " + typeof mets);
            console.log($(mets[0]).text());
            if (mets.length > 0){
              for (var i = 0; i < mets.length; ++i){
                fk.metatags.push($(mets[i]).text());
              }
            }
          },
          'xml');
  });



/**
 * Inserts [tagger] link in each Action
 */
fk.insert_taglink = function(activate_fn) {
  var tr = $(this);
  var action = $(tr.find("td")[3]);
  var tagbutton = $("<a href=\"#\">[tagger]</a>");

  tagbutton.click(function(ev) {
                    ev.preventDefault();
                    /** avoid creating multiple boxes on multiple clicks**/
                    if (action.find(".tagbox").length == 0){
                    activate_fn(tr);
                    }
                  });

  action.append(tagbutton);
};



/** Build, open tag box **/

/** Send new tag info **/