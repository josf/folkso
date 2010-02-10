faboid =  /home/joseph/sect/fdent/js/faboid.js
objects = $(faboid) folksonomie.js

folksonomie.min.js : $(objects)
		cp folksonomie.js temp.js; cat  $(faboid) >> temp.js; 
		java -jar compiler.jar -js temp.js -js_output_file folksonomie.min.js
#  --compilation_level WHITESPACE_ONLY