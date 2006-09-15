To get a readable manual:

$ cd de/
$ autoconf
$ ./configure
$ make

A successful run should print out something like that:

$ make
/opt/local/bin/xsltproc --output html/index.html ./build/html.xsl manual.xml
Computing chunks...
Writing ./crash_course.hello_world.html for sect1(crash_course.hello_world)
Writing ./crash-course.html for chapter(crash-course)
Writing ./licensing.html for appendix(licensing)
Writing ./the.index.html for index(the.index)
Writing ./index.html for book(manual)
Writing HTML.manifest
$ 

You'll find your manual in de/html.