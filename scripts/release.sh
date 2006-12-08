BUILD_NUMBER=`date "+%Y%m%d%H%M"` 

# test if php is available
if [ -z "`which php`" ] ; then
	echo "PHP executable not found"
	exit 1
fi
if [ ! -x "`which php`" ] ; then
	echo "PHP executable not executable"
	exit 1
fi

# remove directories from previous exports
rm -rf wcom-trunk

# create package output directory
mkdir "wcom-$BUILD_NUMBER"

# export trunk
svn export https://www.dotthink.net/svn/Welcompose/trunk wcom-trunk

# cd to wcom trunk
cd wcom-trunk

# move database schema to setup directory
cp database/wcom.sql wcom/setup/wcom.sql

# replace sys.inc.php with sys.inc.php-dist
mv wcom/core/conf/sys.inc.php-dist wcom/core/conf/sys.inc.php

# remove database & doc directory
rm -rf database
rm -rf documentation

# create source packages
tar cvfz "../wcom-$BUILD_NUMBER/wcom-$BUILD_NUMBER-src".tar.gz wcom
tar cvfj "../wcom-$BUILD_NUMBER/wcom-$BUILD_NUMBER-src".tar.bz2 wcom
zip -r "../wcom-$BUILD_NUMBER/wcom-$BUILD_NUMBER-src" wcom

# create compressed package
echo "Creating compressed package"
php scripts/installer/create_install_package.php \
	--compress=true \
	--package-extractor-script=scripts/installer/installer.inc.php \
	--software-directory=wcom \
	--output-file=install-zlib.php \
	--installer-type=web \
	--web-installer-dir=scripts/installer/web_installer

# create uncompressed package
echo "Creating uncompressed package"
php scripts/installer/create_install_package.php \
	--compress=false \
	--package-extractor-script=scripts/installer/installer.inc.php \
	--software-directory=wcom \
	--output-file=install.php \
	--installer-type=web \
	--web-installer-dir=scripts/installer/web_installer

# remove wcom directory
rm -rf wcom

# create wcom directory
mkdir wcom

# move installer files to wcom directory
mv install-zlib.php wcom
mv install.php wcom

# create tarball (gzipped and bzipped) & zip file
tar cvfz "../wcom-$BUILD_NUMBER/wcom-$BUILD_NUMBER".tar.gz wcom
tar cvfj "../wcom-$BUILD_NUMBER/wcom-$BUILD_NUMBER".tar.bz2 wcom
zip -r "../wcom-$BUILD_NUMBER/wcom-$BUILD_NUMBER" wcom

# leave wcom-trunk directory
cd ../

# remove wcom-trunk directory
rm -rf wcom-trunk

echo "Done!"
exit 0