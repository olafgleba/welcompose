Editing
=======

You can use almost every text editor to edit the manual as long as it supports UTF-8. If you like it more comfortable, using Eclipse (www.eclipse.org -- be sure to pick the right package) with the Web Tools Platform (WTP) is a good choice. The Web Tools Platform knows the DocBook DTDs and can help you with syntax completion. There's a visual xml editor too, called Vex. It's available as stand alone application or as Eclipse plugin. You can get it from http://vex.sf.net. It offers a word-processor like interface and produces adequate results.

Building your own manual (Unix-like environments)
=================================================

You'll need at least xsltproc to build your own manual. It's normally part of the development packages of libxml2 (like libxml2-dev oder libxml2-devel).

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

Building your own manual (Windows)
==================================

?