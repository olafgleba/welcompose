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
mkdir "welcompose-$BUILD_NUMBER"

# export trunk
svn export https://www.dotthink.net/svn/Welcompose/trunk wcom-trunk

# cd to wcom trunk
cd wcom-trunk

# move database schema to setup directory
cp database/wcom.sql welcompose/setup/wcom.sql

# replace sys.inc.php with sys.inc.php-dist
mv welcompose/core/conf/sys.inc.php-dist welcompose/core/conf/sys.inc.php

# remove database & doc directory
rm -rf database
rm -rf documentation

# create source packages
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