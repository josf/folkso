/* 
 * Copyright (c) 2010 Joseph Fahey 
 * GNU Public Licence.
 * 
 * JS specific to the myfabula.php page.
 * 
 * Requires jQuery and folksonomie.js. They should be loaded first.
 * 
 * 
 * 
 */



$(document).ready(function()
                  {
                      fK.init({getUserUrl: 'http://localhost/user.php',
                               getTagUrl: 'http://localhost/tag.php',
                               getResUrl: 'http://localhost/resource.php',
                               postTagUrl: 'http://localhost/tag.php',
                               postResUrl: 'http://localhost/resource.php',
                               simpleTagTemplate: "#tagitem",
                               simpleResTemplate: "#resitem",
                               simpleResWrap: "<li class=\"res\">"});

                      fK.oid.logopath = "/logos/";
                      fK.myfab.fn.taglistFromData($("#tagholder"),
                                                  fK.data.myfab);

                      if (fK.myfab.loginStatus) {
                          $("#oidlist").hide();
                      }
                      $("#oidlist").append(fK.oid.providerList());

                  }); // end document.ready

(function() {
     /*
      * the myfab part of the fK namespace should only be used for this page.
      */
     var myfab = window.fK.myfab = {
         loginStatus: false,
         fn: {  
             /**
              * Immediately appends the new elements 
              * 
              * @param target {jQuery} Where the new objects will be appended.
              */
             taglistFromData: function(target, json) {
                 var data = json || fK.data.myfab;
                 if (data) {
                     for (var i = 0; i < data.length; ++i){
                         fK.simpletag(target, 
                                      fK.data.myfab[i].tagdisplay,
                                      fK.data.myfab[i].tagnorm,
                                      fK.data.myfab[i].resid);
                     }
                 }               
             }
         }
     };
 })();