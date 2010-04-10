
test("a test of nothing", function() {
       ok(true, "this test is fine");
     });


$(document).ready(
    function() {

        QUnit.reset = function() {
            $("#main").children().detach();
        };


        module("Checking that jQuery works");
        test("Get qunit-header", function() 
             {
                 var qhead = $("#qunit-header");
                               expect(1);
                               equals(qhead.length,
                                      1,
                                      "Expected length of 1 for qunit-header jQuery object, got " + qhead.length);

                          });

        test("Checking hide and :hidden", function()
             { 
                 expect(7);
                 equal($(".ardvark").length, 1,
                       "Found the usual ardvark");
                 equal($(".ardvark:hidden").length, 0,
                       "Should find nothing because nothing hidden");
                 $(".ardvark").hide();
                 equal($(".ardvark:hidden").length, 1,
                       "Should find the hidden ardvark here");
                 equal($("p:hidden").length, 1,
                       "The paragraph should be hidden too");
                 equal($("p:not(:hidden)").length, 0,
                       "There aren't any non-hidden paragraphs out there");
                 $(".ardvark").show();
                 equal($(".ardvark:hidden").length, 0,
                       "Ardvark should be back now");
                 equal($(".ardvark:not(:hidden)").length, 1,
                       "Ardvar is :not hidden");
             });
        
        test("bool Comp",
             function() 
             {
                 var no = function() {return false;};
                 var yes = fK.fn.boolComp(no);
                 ok(yes(), "yes should be yes");


             });

        module("Kontroller");
         test("Simple Kontrol",
              function() {
                  ok(fK.Ktl,
                     "Ktl should be defined");

                  var K = new fK.Ktl();
                  ok($.isFunction(K.addControl),
                     "addControl should be a function");

                  K.addBasic("stuff", {selector: "p.mypar",
                                         init: function(sel, $place, data) {
                                           $(sel, $place)
                                               .append($("<em>" + data + "</em>"));
                                       },
                                       update: function(sel, $place, data) {
                                           $(sel, $place).find("em").html(data);
                                       }, 
                                       deleteElem: function(sel, $place, data) {
                                           $(sel, $place).find("em").detach();
                                       }
                                      });

                  equal(K.fields.stuff.selector, 
                        "p.mypar",
                        "Should find selector here");

                  ok($.isFunction(K.fields.stuff.deleteElem),
                     "deleteElem not a function");


              });
        
         test("Setup",
              function() 
              {
                  
                  var K = new fK.Ktl($("#main"), 
                                     {resid: 234});
                  equal(K.data.resid, 234,
                        "Should retrieve resid value of 234 here");

              });
         test("setfield",
              function() {
                  var K = new fK.Ktl($("#main"),
                                          {resid: 234});
                  K.addBasic("stuff", {selector: "p.mypar",
                                       init: function(sel, $place, data) {
                                           $(sel, $place)
                                               .append($("<em>" + data + "</em>"));
                                       },
                                       update: function(sel, $place, data) {
                                           $(sel, $place).find("em").html(data);
                                       }, 
                                       deleteElem: function(sel, $place, data) {
                                           $(sel, $place).find("em").detach();
                                       }
                                      });
                  ok(K.fields.stuff,
                     "K.fields.stuff should be defined here");

                  var ff = K.setfield("stuff");
                  ok($.isFunction(ff),
                     "setfield should return a function");

                  ff("hello");
                  equal(K.fields.stuff['newval'], "hello",
                        "newvalue should be set to 'hello' here: " + 
                        K.fields.stuff.newval);

              });


         test("updateAll (basic control)",
              function() {
                  $("#main").html("<p class='zork'>Original</p>");
                  ok($("#main .zork").length == 1,
                    "We need a zork");
                  ok($(".zork", "#main").length == 1);

                  var K = new fK.Ktl($("#main"),
                                          {resid: 234});
                  K.addBasic("stuff", {selector: ".zork",
                                         init: function(sel, $place, data) {
                                             $(sel, $place)
                                                 .append($("<em>" + data + "</em>"));
                                         },
                                         update: function(sel, $place, data) {
                                             $(sel, $place).find("em").html(data);
                                         }, 
                                         deleteElem: function(sel, $place, data) {
                                             $(sel + " em", $place).detach();
                                         }
                                      });
                  var ff = K.setfield("stuff");
                  equal(K.fields.stuff.selector, ".zork",
                        "selector is incorrect");

                  
                  ff("New content");

                  equal(K.fields.stuff['newval'], "New content",
                        "newvalue should be 'New content'" + 
                        K.fields.stuff.newval);

                  K.updateAllNew();
                  equal(K.fields.stuff.value, "New content",
                        'value field should be set to "New content" ' + 
                        K.fields.stuff.value);


                  ok(K.fields.stuff.initialized,
                     "init call should set initialized to true");
                  equal($(K.fields.stuff.selector, K.$place).length, 1,
                        "selectors not selecting right");
                  equal($("#main .zork em").text(), "New content",
                        "should find new content in DOM");


                  /** delete **/
                  var del = K.deletefield("stuff");
                  ok($.isFunction(del), "deletefield does not return function");
                  del();
                  
                  K.updateAllNew();

                  equal($("#main .zork em").length, 0,
                       "content should be gone: " + $("#main .zork em").text());
                  
              });

         test("trigger update",
              function() {
                  
                  $("#main").html("<p class='zork'></p>");
                  var K = new fK.Ktl($("#main"),
                                          {resid: 234});

                  K.addBasic("stuff", {selector: ".zork",
                                         init: function(sel, $place, data) {
                                             $(sel, $place)
                                                 .append($("<em>" + data + "</em>"));
                                         },
                                         update: function(sel, $place, data) {
                                             $(sel, $place).find("em").html(data);
                                         }, 
                                         deleteElem: function(sel, $place, data) {
                                             $(sel, $place).find("em").detach();
                                         }
                                      });
                  var ff = K.setfield("stuff");
                  ff("New content");

                  $(K).trigger("update");
                  equal($("#main .zork").text(), "New content",
                        "Update should have happened here");
              });

        test("appending",
             function(){
                 $("#main").html("<ul>");
                 var K = new fK.Ktl($("#main"));
                 K.addList("stuff", {selector: "ul",
                                     init: function(sel, $place, data) {
                                         $(sel, $place)
                                             .append($("<li>" + data + "</li>"));
                                     }, 
                                     match: function(data) {
                                         return function(item, i) {
                                             return item == data;
                                         };
                                     },
                                     update: function($ob, data) {
                                         $ob.text(data);
                                     }
                                    });

                 /** test setup **/
                 equal($(K.fields.stuff.selector, K.$place).length, 1,
                      "Problem with internal selectors");
                 
                 var appendix = K.appendField("stuff");
                 ok($.isFunction(appendix),
                    "appendix is not a function!");

                 appendix("Hello");

                 equal(K.fields.stuff.appendval, "Hello",
                       "appendval not being set");

                 $(K).trigger("update");
                 ok(K.fields.stuff, "stuff field not defined");


                 equal($("#main li").length, 1,
                       "An li should be there");

                 equal($("#main ul li").text(), "Hello",
                       "Should really say 'Hello', not: " 
                      + $("#main ul li").text());

                 var dropList = K.restartList("stuff");
                 dropList();

                 equal($("#main li").length, 0,
                       "restartList function should remove all list items");

             });

        test("appending (mostly deleting)",
             function()
             {

                 $("#main").html("<ul>");
                 var K = new fK.Ktl($("#main"));
                 K.addList("stuff", {selector: "ul",
                                     init: function(sel, $place, data) {
                                         $(sel, $place)
                                             .append($("<li>" + data + "</li>"));
                                     }, 
                                     match: function(data) {
                                         return function(item, i) {
                                             return item == data;
                                         };
                                     },
                                     update: function($ob, data) {
                                         $ob.text(data);
                                     }
                                    });


                 var matcher = K.fields.stuff.match("zork");
                 ok(matcher("zork", 1),
                    "Matcher function should match what matches");


                 var things = ["Hello", "Goodbye"],
                 newth = $.grep(things, function(th, idx) { 
                                    return th == "Hello"; 
                                });
                 ok($.inArray(newth, "Hello"),
                    "Hello should still be there");


                 var newthth = $.grep(things, function(th, idx) {
                                          return th == "Goodbye"; 
                                          });
                 ok($.inArray(newthth, "Hello"),
                    "Hello should still be there");


                 


                 var appendix = K.appendField("stuff");
                 var deletix = K.deletefield("stuff");
                 ok($.isFunction(deletix), "Delete field should return a function");

                 deletix("Hello");
                 equal(K.fields.stuff.removeVal, "Hello",
                       "removeVal should have be updated by the deletefield function");

                 $(K).trigger("update");
                 equal($("#main ul li").length, 0,
                       "deleting should remove the elements");

                 appendix("Hello");
                 appendix("Goodbye");

                 equal(K.fields.stuff.appendval.length, 2,
                       "After multiple appends, should have appendval array length of 2");

                 $(K).trigger("update");

                 equal(K.fields.stuff.value.length, 2,
                       "Value array should be extended too");

                 equal($("#main ul li").length, 2,
                       "Successive appends should keep adding list items");

                 deletix("Hello");

                 $(K).trigger("update");

                 equal(K.fields.stuff.value.length, 1,
                       "value array should be shortened on delete");

                 equal($("#main ul li").length, 1,
                       "Deleting should still work");

                 $(K).trigger("update");
                 deletix("Goodbye");

                 $(K).trigger("update");
                 equal($("#main ul li").length, 0,
                       "Deleting should still work (second delete)");

             });


        test("appending (mostly updating)",
             function() 
             {

                 $("#main").html("<ul>");
                 var K = new fK.Ktl($("#main"));
                 K.addList("stuff", {selector: "ul",
                                     init: function(sel, $place, data) {
                                         $(sel, $place)
                                             .append($("<li>" + data + "</li>"));
                                     }, 
                                     match: function(data) {
                                         return function(item, i) {
                                             return item == data;
                                         };
                                     },
                                     update: function($ob, data) {
                                         $ob.text(data);
                                     }
                                    });
                 var appendix = K.appendField("stuff");
                 appendix("Hello");
                 appendix("Goodbye");
                 $(K).trigger("update");

                 var updatix = K.updateField("stuff");
                 updatix("Goodbye", "So long");

                 ok($.isFunction(updatix),
                    "updateField did not produce a function");

                 equal(K.fields.stuff.updateVals[0].newthing, "So long",
                       "update data should be in updateVals");

                 $(K).trigger("update");
                 ok(/So long/.test($("#main ul li").text()),
                    "Updated text not appearing: " + $("#main ul li").text());
                 
                 

             });


        module("Trying Functional.js");
        test("Simple Functional", function()
             {
                 ok(Functional,
                    "No 'Functional' object");
                 function zork() {
                     var donothing;
                     return 1;
                 }
                 // not using Functional.install: installing all of
                 // Functional breaks jQuery
                 ok(typeof zork.partial == "function",
                    "No partial in prototype");

             });


        module("Trying jqote");

        test("Basic jqote", function()
             {
                 expect(4);
                 var templ = $("#template");
                 var one = 1;
                 equals(templ.length,
                        1,
                        "Did not find #template, templ has length of " 
                        + templ.length);
                 
                 ok(typeof templ.jqote == "function",
                    "jQuery.jqote is not a function");
                 ok(typeof templ.jqote({name: 'Zork'}) == "object",
                    "jqote not returning an object");
                 var lvt = templ.jqote({name: 'Vonk'});
                 $(templ.jqote({name: 'Zork'}))
                     .appendTo($("#template-test"));
                 var re = /Yo.*Zork/;
                 ok(re.test($("#template-test").text()),
                    "Not getting correct template output in DOM");
             });
        test("jqote with tag template", function () 
             {
                 expect(7);
                 var templ = $("#simpletag"); 

                 // found template?
                 equals(templ.length, 
                        1,
                        "Incorrect number of templates found, should be 1, not " + templ.length);
                 $(templ.jqote({tagnorm: "taggage",
                                display: "Taggage",
                                id: 1243})).appendTo($("#template-test"));
                 var testr = /taggage/;
                 ok(testr.test($("#template-test").html()),
                    "Did not find tagnorm value  in #template-test");
                 var testr2 = />Taggage</;
                 ok(testr.test($("#template-test").html()),
                    "Did not find display value in #template-test");

                 var testr3 = /I am a tag/;
                 ok(testr.test($("#template-test").html()),
                    "Did not find template boilerplate in #template-test");

                 var t2 = $("#simpleres");
                 equals(t2.length, 1,
                        "Incorrect number of res templates found, should "
                        + " be 1, not " + t2.length);
                 
                 equals($("#restarget").length, 1,
                        "Did not find #restarget");

                 $(t2.jqote({url: "http://example.com",
                             display: "Examples for all",
                             id: 12355})).appendTo($("#restarget"));

                 var testr4 = /Examples for all/;
                 ok(testr4.test($("#restarget").html()),
                    "Did not find resource title data in #restarget");

                 /* cleanup */
                 $("#restarget").html("");
                 
             });
        
        module("folksonomie.js intialization");
        test("Basic init", function() 
             {
                 expect(7);
                 ok(fK,
                    "fK is undefined apparently");

                 ok(typeof fK == "object",
                    "fK should be an object");

                 ok(typeof fK.init == "function",
                    "fK.init should be a function");
                 fK.init({hoohaa: "bob", 
                          postTagUrl: "http://example.com",
                          simpleResTemplate: "#simpleres"});

                 ok(fK.cf.simpleResTemplate,
                    "simpleResTemplate not showing up");

                 ok(typeof fK.cf.simpleResTemplate == "object",
                    "fK.cf.simpleResTemplate should be an object, not a "
                    + typeof fK.cf.simpleResTemplate);

                 equal(fK.cf.simpleResTemplate.length,
                       1,
                       "Not finding simpleResTemplate from initialization");
                 equal(fK.cf.postTagUrl,
                       "http://example.com",
                       "fK.cf.postTagUrl should be initalized as "
                       + "http://example.com and not " + fK.cf.postTagUrl);
             });

        module("folksonomie.js template activation");
        test("simpleres", function()
             {
                 expect(3);
                 fK.init({simpleResTemplate: "#simpleres"});
                 fK.simpleres($("#restarget"),
                              "Folksonomie rocks",
                              "http://example.com",
                              55);
                 var re1 = /rocks/;
                 ok(re1.test($("#restarget").html()),
                    "Not finding \"rocks\" in #restarget");

                 var re2 = /Resource/;
                 ok(re2.test($("#restarget").html()),
                    "Not finding template boilerplate in #restarget");

                 var re3 = /example\.com/;
                 ok(re3.test($("#restarget").html()),
                    "Not finding url in #restarget");

             });
        test("simpletag", function()
             {
                 expect(7);
                 fK.init({simpleTagTemplate: "#simpletag",
                          postTagUrl: "http://localhost/tag.php",
                          getTagUrl: "http://localhost/tag.php",
                          postResUrl: "http://localhost/resource.php",
                          getResUrl: "http://localhost/resource.php",
                          getUserUrl: "http://localhost/user.php",
                          simpleResWrap: "<li class=\"res\">"
                         });
                 fK.simpletag($("#restarget"),
                              "Taggage",
                              "taggage",
                              65);
                 ok(/Taggage/.test($("#restarget").html()),
                    "Tag display not showing up");
                 ok(/taggage/.test($("#restarget").html()),
                    "tagnorm not showing up");
                 ok(/tag\.php/.test(fK.cf.getTagUrl),
                    "Incorrect value for getTagUrl");
                 ok(/tag\.php/.test(fK.cf.postTagUrl),
                    "Incorrect value for postTagUrl");
                 ok(/resource\.php/.test(fK.cf.getResUrl),
                    "Incorrect value for getResUrl");
                 ok(/resource\.php/.test(fK.cf.postResUrl),
                    "Incorrect value for postResUrl");
                 ok(/user\.php/.test(fK.cf.getUserUrl),
                    "Incorrect value for getUserUrl");

             });
        test("Storing data in tag/res DOM elements", function()
             {
                 expect(9);

                 var bog = $("<p class=\"bogus\">Stuff</p>");
                 bog.data("stuff", {things: "stuff"});

                 equal(bog.data("stuff").things, "stuff",
                       "Deep problem with getting data back on bogus test case");
                 $("#restarget").append(bog);
                 var newbog = $("#restarget").find("p.bogus");

                 equal($(newbog).data("stuff").things, "stuff",
                       "Attached element has no data");

                 var res = $("#restarget").find("div.ares"),
                 tag = $("#restarget").find(".atag");
                 tag.data("test", 55);
                 tag.data("test2", {some: "thing", elste: "no"});

                 equal(tag.length, 1, "Not finding p.simpletag");
                 equal(res.length, 1, "Not finding p.simpleres");

                 equal(tag.data("test"), 55,
                       "Basic data retreival not working (testcase)");
                 equal(tag.data("test2").some, "thing",
                       "Retreival of object not working");

                 var tagdata = tag.data("fKtag");

                 ok(typeof tagdata == "object",
                    "Should get object back from .data retreival");
                 equal(tagdata.id,
                       65,
                       "Did not correctly retrieve .data for tag "
                       + "expected 65, got " + $(tag[0]).data("fKtag").id);
                 
                 equal(res.data("fKres").id,
                       55,
                       "Incorrect id in resource data");

             });
        module("Ajax object builders");
        test("tagGetObject", function() 
             {
                 expect(6);
                 var thing = 
                     fK.fn.tagGetObject({ 
                                            folksothis: "a", folksothat: "b" },
                                        function() { return 1; },
                                        function() { return 2; });
                 ok(thing.data, "Data object is missing");
                 equal(thing.data.folksothis, "a",
                       "Incorrect data back, expecting 'a'");
                 ok($.isFunction(thing.success), 
                    "success should be a function");
                 ok($.isFunction(thing.error),
                    "error should be a function");
                 ok(thing.url, "Url should be present");
                 ok(thing.type, "type should be present");
             });


        test("resPostObject", function()
             {

                 expect(7);
                 var
                 suck = function() { return 1; },
                 fail = function() { return 2; },
                 thing = 
                     fK.fn.resPostObject({
                                             folksothis: "a",
                                             folksothat: "b"
                                         },
                                         suck,
                                         fail);

                 ok(thing.data, "Data object is missing");
                 equal(thing.data.folksothis, "a",
                       "Incorrect data back, expecting 'a'");
                 ok($.isFunction(thing.success), 
                    "success should be a function");
                 ok($.isFunction(thing.error),
                    "error should be a function");
                 ok(thing.url, "Url should be present");
                 ok(thing.type, "type should be present");

                 equal(thing.url, "http://localhost/resource.php",
                       "Incorrect url set");

             });

        module("Basic event stuff");
        test("droptag_react (without ajax)", function() 
             {
                 expect(2);
                 var button = $($("#restarget").find(".droptag")[0]);
                 var dr = fK.fn.droptag_react({tagnorm: 'zook',
                                               display: 'Zook',
                                               id: 12,
                                               element: button});
                 button.click(dr);
                 ok(typeof dr == "function",
                    "droptag_react not returning function");

                 equal(dr.length, 1,
                       "droptag_react should return a function taking 1 arg");

             });

        test("tagres_react (without server)", function() 
             {
                 expect(1);
                 var button = $(".droptag", "#restarget"),
                 tagit = fK.fn.tagres_react($("#restarget"), 
                                            $("#restarget"));
                 ok(typeof tagit == "function",
                    "tagres_react not returning function");
             });

        /* setup ajax call  */
        var ibox = $("<input type=\"text\">"),
        target = $("<ul>"),
        tagbutton = $("<a href=\"#\">");

        ibox.val("tagone");
        tagbutton.click(fK.fn.tagres_react(ibox, target));

        /*                      asyncTest("tagres_react (with server)", 1, function()
         {
         tagbutton.trigger("click");
         ok(/tagone/.test(target.html()),
         "Did not find 'tagone' in target list");
         start();
         });*/
        


        test("Error function composing", function()
             {
                 expect(4);
                 var f1 = function() { return 1; };
                 f1.errorcode = 404;
                 var f2 = function() { return 2; };
                 f2.errorcode = 500;
                 var f3 = function() { return 3; }; //default

                 var efunk = fK.fn.errorChoose(f1, f2, f3);

                 var fake500 = {status: 500}, fake200 = {status: 200},
                 fake404 = {status: 404};
                 ok(efunk,
                    "efunk not defined: no function returned");
                 equal(efunk(fake500),
                       2,
                       "Incorrect result from error choosing function");
                 equal(efunk(fake200),
                       3,
                       "Default error function not being called");
                 equal(efunk(fake404),
                       1,
                       "First error function not being called");
             });

        module("tag expansion");
        test("displayJsonResList", function() 
             {

                 var longJson = 
                     [
                         {"resid":"6","url":"http:\/\/dynamic.example.com\/1","title":"A page, a page"}
                         ,{"resid":"19","url":"http:\/\/dynamic.example.com\/14","title":"That is what you say"}
                         ,{"resid":"20","url":"http:\/\/dynamic.example.com\/15","title":"No, that is what you say"}
                         ,{"resid":"21","url":"http:\/\/dynamic.example.com\/16","title":"Well too bad"}
                         ,{"resid":"22","url":"http:\/\/dynamic.example.com\/17","title":"Sometimes there are just errors"}
                         ,{"resid":"23","url":"http:\/\/dynamic.example.com\/18","title":"I know"}
                         ,{"resid":"24","url":"http:\/\/dynamic.example.com\/19","title":"That is the way it goes"}
                         ,{"resid":"25","url":"http:\/\/dynamic.example.com\/20","title":"There you are"}
                         ,{"resid":"26","url":"http:\/\/dynamic.example.com\/21","title":"Well?"}
                         ,{"resid":"27","url":"http:\/\/dynamic.example.com\/22","title":"Harumph"}
                         ,{"resid":"28","url":"http:\/\/dynamic.example.com\/23","title":"Heck"}
                         ,{"resid":"18","url":"http:\/\/dynamic.example.com\/13","title":"Well that is not my problem"}
                         ,{"resid":"17","url":"http:\/\/dynamic.example.com\/12","title":"But it is"}
                         ,{"resid":"7","url":"http:\/\/dynamic.example.com\/2","title":"Fun with resources"}
                         ,{"resid":"8","url":"http:\/\/dynamic.example.com\/3","title":"Something to look at"}
                         ,{"resid":"9","url":"http:\/\/dynamic.example.com\/4","title":"OMG!"}
                         ,{"resid":"10","url":"http:\/\/dynamic.example.com\/5","title":"WTF!"}
                         ,{"resid":"11","url":"http:\/\/dynamic.example.com\/6","title":"How bout that?"}
                         ,{"resid":"12","url":"http:\/\/dynamic.example.com\/7","title":"Can you imagine?"}
                         ,{"resid":"13","url":"http:\/\/dynamic.example.com\/8","title":"No I cannot imagine at all"}
                         ,{"resid":"14","url":"http:\/\/dynamic.example.com\/9","title":"That just bowls me over"}
                         ,{"resid":"15","url":"http:\/\/dynamic.example.com\/10","title":"What are you talking about?"}
                         ,{"resid":"16","url":"http:\/\/dynamic.example.com\/11","title":"This should not be wrong"}
                         ,{"resid":"29","url":"http:\/\/dynamic.example.com\/24","title":"Fudge"}
                     ];


                 var bogusTdata = {element: $("#stuff"),
                                   title: "The title",
                                   url: "http://url.com",
                                   resid: 1234},
                 bogusTarget = $("<ul>");
                 ok(typeof fK.fn.displayJsonResList(bogusTarget) == "function",
                    "displayJsonResList() should return a function");

                 var tdata = {element: $("#reslistholder")},
                 json = [
                     {title: "Hey", url: "http://example.com/huh",
                      resid: 1234},
                     {title: "What?", url: "http://example.com/what",
                      resid: 4321}
                 ];
                 var newf = fK.fn.displayJsonResList($("#reslistholder"));
                 newf(json);
                 
                 var first = $("#reslistholder").html();
                 ok(/Hey/.test(first),
                    "Not finding title in list generated from json");
                 ok(/example.com\/huh/.test(first),
                    "Not finding url in list generated from json");

                 $("#reslistholder").children().remove();
                 ok($("li", "#reslistholder").length == 0,
                    "reslistholder should be empty for the next test");

                 newf(longJson);
                 equal($("li", "#reslistholder").length, 10,
                       "Should be exactly ten list elements here");

                 equal($("#reslistholder").data("starting"), 0, 
                       "reslistholder-data-starting should be 0");
                 equal($("#reslistholder").data("ending"), 9,
                       "reslistholder-data-ending should be 10 here");


                 // advance 1: 
                 fK.fn.advance1($("#reslistholder"));
                 //                               $("li:first", "#reslistholder").hide();
                 equal($("li", "#reslistholder").length, 11,
                       "advance1 did not add an li element");
                 equal($("li:hidden", "#reslistholder").length, 1,
                       "there should be exactly 1 hidden element here");
                 equal($("li:visible", "#reslistholder").length, 10, 
                       "should still have exactly 10 visible items here");
                 ok($("li:first", "#reslistholder").is(":hidden"),
                    "First element should be hidden");
                 
                 // advance once more
                 fK.fn.advance1($("#reslistholder")); 
                 ok($("li", "#reslistholder").eq(1).is(":hidden"),
                    "2nd element should be hidden");
                 equal($("li:visible", "#reslistholder").length, 10, 
                       "Advance2: should still have exactly 10 visible items here");

                 // rewind
                 fK.fn.rewind1($("#reslistholder"));
                 equal($("li:visible", "#reslistholder").length, 10,
                       "Rewind 1: There should always be 10 visible elements");
                 ok($("li", "#reslistholder").eq(1).is(":visible"),
                    "2nd list element should be visible");
                 equal( $("li:hidden", "#reslistholder").length, 2,
                        "there is one last not hidden");
                 ok($("li", "#reslistholder").eq(11).is(":hidden"),
                    "12th (index 11) list element should be hidden");

                 // advance again (should be making
                 // visible, not building new elements
                 fK.fn.advance1($("#reslistholder"));
                 equal($("li:visible", "#reslistholder").length, 10,
                       "Still just 10 visible elements");
                 equal($("li", "#reslistholder").length, 12,
                       "Should still only be 12 elements total");
                 // means we did not make any extra new ones

                 var shortJson = longJson.slice(0, 6);
                 equal(shortJson.length, 6,
                       "Our shortJson test object should be 6 long");


             });

        test("basic expand tag event stuff", function()
             {
                 expect(1);
                 ok(typeof fK.fn.expandtag_react(
                        {url: "z", resid: 123, title: "xyz"}
                    ) == 'function',
                    "expandtag_react should return a function");
             });

        test("resource delete - basic stuff", function()
             {
                 expect(1);
                 ok(typeof fK.fn.dropres_react == "function",
                    "dropres_react not returning a function");
             });


        test("Tag cloud formatting (no ajax)", function()
             {
                 var targ = $("<ul>");
                 fK.fn.buildCloud(targ, "http://nothing.com",
                                  '<?xml version="1.0"?> <tagcloud resource="http://www.fabula.org/actualites/article13644.php">  <tag> <numid>13012</numid> <display>Dessons, Gérard</display> <link href="http://www.fabula.org/tags/tag.php/folksotag=dessons-gerard&amp;folksodatatype=html" rel="alternate"/><weight>1</weight> <tagnorm>dessons-gerard</tagnorm></tag>  <tag> <numid>5781</numid> <display>Imitation</display> <link href="http://www.fabula.org/tags/tag.php/folksotag=imitation&amp;folksodatatype=html" rel="alternate"/><weight>5</weight> <tagnorm>imitation</tagnorm></tag>  <tag> <numid>6165</numid> <display>Peinture</display> <link href="http://www.fabula.org/tags/tag.php/folksotag=peinture&amp;folksodatatype=html" rel="alternate"/><weight>5</weight> <tagnorm>peinture</tagnorm></tag>  <tag> <numid>15145</numid> <display>Rembrandt</display> <link href="http://www.fabula.org/tags/tag.php/folksotag=rembrandt&amp;folksodatatype=html" rel="alternate"/><weight>2</weight> <tagnorm>rembrandt</tagnorm></tag>  <tag> <numid>7551</numid> <display>spectateurs</display> <link href="http://www.fabula.org/tags/tag.php/folksotag=spectateurs&amp;folksodatatype=html" rel="alternate"/><weight>4</weight> <tagnorm>spectateurs</tagnorm></tag>  <tag> <numid>6483</numid> <display>Tableau</display> <link href="http://www.fabula.org/tags/tag.php/folksotag=tableau&amp;folksodatatype=html" rel="alternate"/><weight>2</weight> <tagnorm>tableau</tagnorm></tag>');

                 expect(4);
                 ok($("li", targ).length > 2,
                    "Did not find at least 3 list elements added");
                 ok(/Tableau/.test(targ.html()),
                    "Did not find display element ('Tableau') in cloud");
                 ok(/cloudclass/.test(targ.html()),
                    "Did not find cloudclass in cloud");
                 ok(/www\.fabula\.org\/tags/.test(targ.html()),
                    "Did not find url in cloud");
             });

        module("Login status");
        test("pollFolksoCookie", function()
             {
                 fK.fn.pollFolksoCookie();
                 
                 /* Test disabled because only works if you manually remove cookie first      
                  ok(! /folksosess/.test(document.cookie),
                  "Cookie should not be set yet"); */
                 ok(! fK.loginStatus,
                    "fK.loginStatus should be false here");
             });
        asyncTest("Going to add the cookie", function()
                  {
                      document.cookie = document.cookie + "folksosess=stuffyouowant";
                      ok(! fK.loginStatus,
                         "fK.loginStatus should still be false here");
                      setTimeout(
                          function() {
                              ok(/folksosess/.test(document.cookie),
                                 "cookie should be set now");
                              ok(fK.loginStatus,
                                 "fK.loginStatus should have been set to true");
                              start();
                          }, 2000);
                  });



    });



