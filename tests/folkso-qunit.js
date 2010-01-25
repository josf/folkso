

test("a test of nothing", function() {
       ok(true, "this test is fine");
     });


$(document).ready(function() {
                      module("Checking that jQuery works");
                      test("Get qunit-header", function() 
                           {
                               var qhead = $("#qunit-header");
                               expect(1);
                               equals(qhead.length,
                                      1,
                                      "Expected length of 1 for qunit-header jQuery object, got " + qhead.length);
                          });

                      module("Trying Functional.js");
                      test("Simple Functional", function()
                           {
                               expect(2);
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

                      test("Error function composing", function()
                           {
                               expect(4);
                               var f1 = function() { return 1; };
                               f1.errorcode = 404;
                               var f2 = function() { return 2; };
                               f2.errorcode = 500;
                               var f3 = function() { return 3; }; //default

                               var efunk = fK.fn.errorChoose(f1, f2, f3);
                               ok(efunk,
                                  "efunk not defined: no function returned");
                               equal(efunk(500),
                                     2,
                                     "Incorrect result from error choosing function");
                               equal(efunk(200),
                                     3,
                                     "Default error function not being called");
                               equal(efunk(404),
                                     1,
                                     "First error function not being called");
                           });

                      module("tag expansion");
                      test("displayJsonResList", function() 
                           {
                               expect(3);
                               var bogusTdata = {element: $("#stuff"),
                                                 title: "The title",
                                                 url: "http://url.com",
                                                 resid: 1234};
                               ok(typeof fK.fn.displayJsonResList(bogusTdata) == "function",
                                  "displayJsonResList() should return a function");

                               var tdata = {element: $("#reslistholder")},
                               json = [
                                   {title: "Hey", url: "http://example.com/huh",
                                    resid: 1234},
                                   {title: "What?", url: "http://example.com/what",
                                    resid: 4321}
                               ];
                               var newf = fK.fn.displayJsonResList(tdata);
                               newf(json);
                               
                               var first = $("#reslistholder").html();
                               ok(/Hey/.test(first),
                                  "Not finding title in list generated from json");
                               ok(/example.com\/huh/.test(first),
                                  "Not finding url in list generated from json");

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
                  });




