

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
                               expect(4);
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
                           });
                               
                      module("folksonomie.js intialization");
                      test("Basic init", function() 
                           {
                               expect(4);
                               ok(fK,
                                  "fK is undefined apparently");

                               ok(typeof fK == "object",
                                  "fK should be an object");

                               ok(typeof fK.init == "function",
                                  "fK.init should be a function");
                               fK.init({hoohaa: "bob", postTagUrl: "http://example.com"});
                               equal(fK.cf.postTagUrl,
                                     "http://example.com",
                                     "fK.cf.postTagUrl should be initalized as "
                                     + "http://example.com and not " + fK.cf.postTagUrl);
                           });

});


     