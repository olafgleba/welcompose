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
rm -rf oak-trunk

# create package output directory
mkdir "oak-$BUILD_NUMBER"

# export trunk
svn export https://www.dotthink.net/svn/Oak/trunk oak-trunk

# cd to oak trunk
cd oak-trunk

# move database schema to setup directory
cp database/oak.sql oak/setup/oak.sql

# replace sys.inc.php with sys.inc.php-dist
mv oak/core/conf/sys.inc.php-dist oak/core/conf/sys.inc.php

# remove database & doc directory
rm -rf database
rm -rf documentation

# create source packages
tar cvfz "../oak-$BUILD_NUMBER/oak-$BUILD_NUMBER-src".tar.gz oak
tar cvfj "../oak-$BUILD_NUMBER/oak-$BUILD_NUMBER-src".tar.bz2 oak
zip -r "../oak-$BUILD_NUMBER/oak-$BUILD_NUMBER-src" oak

# create compressed package
echo "Creating compressed package"
php scripts/installer/create_install_package.php \
	--compress=true \
	--package-extractor-script=scripts/installer/installer.inc.php \
	--software-directory=oak \
	--output-file=install-zlib.php \
	--installer-type=web \
	--web-installer-dir=scripts/installer/web_installer

# create uncompressed package
echo "Creating uncompressed package"
php scripts/installer/create_install_package.php \
	--compress=false \
	--package-extractor-script=scripts/installer/installer.inc.php \
	--software-directory=oak \
	--output-file=install.php \
	--installer-type=web \
	--web-installer-dir=scripts/installer/web_installer

# remove oak directory
rm -rf oak

# create oak directory
mkdir oak

# move installer files to oak directory
mv install-zlib.php oak
mv install.php oak

# create tarball (gzipped and bzipped) & zip file
tar cvfz "../oak-$BUILD_NUMBER/oak-$BUILD_NUMBER".tar.gz oak
tar cvfj "../oak-$BUILD_NUMBER/oak-$BUILD_NUMBER".tar.bz2 oak
zip -r "../oak-$BUILD_NUMBER/oak-$BUILD_NUMBER" oak

# leave oak-trunk directory
cd ../

# remove oak-trunk directory
rm -rf oak-trunk

echo "Done!"
exit 0