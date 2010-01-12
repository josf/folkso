

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

                       test("jQote hack: not using a bogus script element", function()
                           {
                               expect(8);
                               var templ = $("<p>Hoohaa <,= this.name ,></p>");

                               ok(typeof templ.jqote == "function",
                                  "No jqote function here");
                               var re1 = /Hoohaa/;
                               ok(re1.test(templ.html()),
                                  "Did not find template text in template");

                               var re2 = /<,/;
                               ok(re2.test(templ.html()),
                                  "Did not find <, in template");

                               var temptest = $("#jqote-hack");
                               equal(temptest.length,
                                     1,
                                     "jqote-hack element not found");

                               templ.jqote({name: "testperson"}, ",")
                                   .appendTo(temptest);

                               var re = /Hoohaa.*testperson/;
                               ok(re.test(temptest.text()),
                                  "Incorrect template output when not using bogus script element");
                               

                           });
});


     