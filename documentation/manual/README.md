# Editing #

You can use almost every text editor to edit the manual as long as it supports UTF-8. If you like it more comfortable, using Eclipse (www.eclipse.org -- be sure to pick the right package) with the Web Tools Platform (WTP) is a good choice. The Web Tools Platform knows the DocBook DTDs and can help you with syntax completion. There's a visual xml editor too, called Vex. It's available as stand alone application or as Eclipse plugin. You can get it from http://vex.sf.net. It offers a word-processor like interface and produces adequate results.


## Building your own manual (Unix-like environments) ##

You'll need at least xmllint and xsltproc to build a html version of the manual. xmllint is normally part of a package like libxml2-utils. xsltproc is part of the development packages of libxml2 like libxml2-dev or libxml2-devel.

To build a manual, cd to the manual directory and type:

    $ autoconf
    $ ./configure 
    $ make html-chunk

Getting a pdf from the xml sources is a bit trickier. You'll need a recent Java VM and Apache FOP (http://xmlgraphics.apache.org/fop/). Pick a binary distribution (they include "-bin" in their names) of FOP 0.20.x. DO NOT USE A NEWER VERSION LIKE 0.91. THEY WON'T WORK AT THE MOMENT. After you unpacked the binary distribution, make sure JAVA_HOME points to the home directory of the Java VM. To generate the PDF, use:

    $ cd manual/de
    $ autoconf
    $ ./configure --with-fop=/path/to/fop/fop.sh
    $ make pdf

To remove the generated manual, type:

    $ make clean

If you like to build all the manuals at one, use the makefile in this directory here. Simply type:

    $ make html-chunk

If you like to build one chapter per site, use the makefile in this directory here. Simply type:

    $ make html-single-page

It will go through every subdirectory which name starts with "manual" and build the chunked html manual. Building other formats (pdf etc.) is not possible at the moment.

To remove all the generated manuals, type:

    $ make clean
