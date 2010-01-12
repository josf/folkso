

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
                               $(templ.jqote({name: 'Zork'}))
                                   .appendTo($("#template-test"));
                               var re = /Yo.*Zork/;
                               ok(re.test($("#template-test").text()),
                                  "Not getting correct template output in DOM");
                           });
});


     