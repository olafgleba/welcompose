all:
	for lang in manual*/. ; do $(MAKE) -f ../Makefile -C "$$lang" build-all ; done

all-html-chunk:
	for lang in manual*/. ; do $(MAKE) -f ../Makefile -C "$$lang" build-all-html-chunk ; done

all-pdf:
	for lang in manual*/. ; do $(MAKE) -f ../Makefile -C "$$lang" build-all-pdf ; done

clean:
	for lang in manual*/. ; do $(MAKE) -f ../Makefile -C "$$lang" clean-all ; done

build-all:
	autoconf 
	sh ./configure
	$(MAKE)

build-all-html-chunk:
	autoconf 
	sh ./configure
	$(MAKE) html-chunk

build-all-pdf:
	autoconf 
	sh ./configure
	$(MAKE) pdf

clean-all:
	autoconf
	sh ./configure
	$(MAKE) clean
