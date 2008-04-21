BUILD_NUMBER=`date "+%Y%m%d%H%M"` 

# import repository url
if [ -z "$1" ] ; then
	echo "No URL to Welcompose repository specified."
	exit 1
fi
WELCOMPOSE_REPOS="$1"

# test if php is available
if [ -z "`which php`" ] ; then
	echo "PHP executable not found"
	exit 1
fi
if [ ! -x "`which php`" ] ; then
	echo "PHP executable not executable"
	exit 1
fi

# test if python is available
if [ -z "`which python`" ] ; then
	echo "Python executable not found"
	exit 1
fi

# remove directories from previous exports
rm -rf wcom-trunk

# create package output directory
mkdir "welcompose-$BUILD_NUMBER"

# export trunk
svn export "$WELCOMPOSE_REPOS" wcom-trunk

# cd to wcom trunk
cd wcom-trunk

# move database schema to setup directory
cp database/wcom.sql welcompose/setup/wcom.sql
if [ -d welcompose/update/tasks ] ; then
	LAST_TASK=`find welcompose/update/tasks -name "*.php" | sort -r | head -n 1 | perl -p -e 's/(.*)([0-9]{4})-([0-9]{3})\.php$/\2-\3/g'`
else
	LAST_TASK=""
fi
perl -p -i -e 's/\@\@schema_version\@\@/'$LAST_TASK'/g' welcompose/setup/wcom.sql

# replace sys.inc.php with sys.inc.php-dist
mv welcompose/core/conf/sys.inc.php-dist welcompose/core/conf/sys.inc.php

# remove database & doc directory
rm -rf database
rm -rf documentation

# create full source packages
tar cvfz "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER-full-src".tar.gz welcompose
tar cvfj "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER-full-src".tar.bz2 welcompose
zip -r "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER-full-src" welcompose

# remove update directory
rm -rf welcompose/update

# compress js
svn export http://svn.devjavu.com/welcompose/trunk/scripts/third_party/jsmin.py jsmin.py
if [ ! -f "jsmin.py" ] ; then
	echo "JavaScript minifier not found. Download failed, eh?"
	exit 1
fi
for file in `find welcompose -type f -name "*.js"` ; do
	tmpfile="$file"-jsmin
	cp "$file" "$tmpfile"
	python jsmin.py < "$tmpfile"  > "$file"
	rm -f "$tmpfile"
done
rm -rf jsmin.py

# create source packages without updater and with compressed js
tar cvfz "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER-src".tar.gz welcompose
tar cvfj "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER-src".tar.bz2 welcompose
zip -r "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER-src" welcompose

# create compressed package
echo "Creating compressed package"
php scripts/installer/create_install_package.php \
	--compress=true \
	--package-extractor-script=scripts/installer/installer.inc.php \
	--software-directory=welcompose \
	--output-file=install-zlib.php \
	--installer-type=web \
	--web-installer-dir=scripts/installer/web_installer

# create uncompressed package
echo "Creating uncompressed package"
php scripts/installer/create_install_package.php \
	--compress=false \
	--package-extractor-script=scripts/installer/installer.inc.php \
	--software-directory=welcompose \
	--output-file=install.php \
	--installer-type=web \
	--web-installer-dir=scripts/installer/web_installer

# remove welcompose directory
rm -rf welcompose

# create welcompose directory
mkdir welcompose

# move installer files to welcompose directory
mv install-zlib.php welcompose
mv install.php welcompose

# create tarball (gzipped and bzipped) & zip file
tar cvfz "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER".tar.gz welcompose
tar cvfj "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER".tar.bz2 welcompose
zip -r "../welcompose-$BUILD_NUMBER/welcompose-$BUILD_NUMBER" welcompose

# leave wcom-trunk directory
cd ../

# remove wcom-trunk directory
rm -rf wcom-trunk

echo "Done!"
exit 0