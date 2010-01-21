/* 
 * Copyright (c) 2010 Joseph Fahey 
 * GNU Public Licence.
 * 
 * JS specific to the myfabula.php page.
 * 
 * Requires jQuery and folksonomie.js
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
                              simpleTagTemplate: "#tagitem"});

                      var tagholder = $("#tagholder");
                      if (fK.data.myfab) {
                          for (var i = 0; i < fK.data.myfab.length; ++i){
                              fK.simpletag(tagholder, 
                                           fK.data.myfab[i].tagdisplay,
                                           fK.data.myfab[i].tagnorm,
                                          fK.data.myfab[i].resid);

                          }
                      }



                  }); // end document.ready