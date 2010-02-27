faboid =  /home/joseph/sect/fdent/js/faboid.js
objects = $(faboid) folksonomie.js

folksonomie.min.js : max
		java -jar compiler.jar -js folksonomie.max.js -js_output_file folksonomie.min.js; \
#  --compilation_level WHITESPACE_ONLY

folksonomie.max.js : $(objects)
		cp folksonomie.js folksonomie.max.js; \
		cat $(faboid) >> folksonomie.max.js; 


min: folksonomie.min.js
max: folksonomie.max.js
		cp folksonomie.max.js folksonomie.min.js

