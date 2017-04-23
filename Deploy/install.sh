#!/bin/sh

Release=$1

cd /data/www/sites/$Release/cairn
rm -f evidensseConfigurator
ln -s ../../EvidensseConfigurator/public_html ./evidensseConfigurator
cd static
rm -f includes
ln -s ../../../cairn_includes ./includes
cd ../Config
cp -a /data/www/sites/Cairnfo_V2/Config/www.cairn.info.ini .
cp -a /data/www/sites/Cairn_Int/Config/www.cairn-int.info.ini .
cd /data/www/sites
rm -f Cairnfo_V2
ln -s ./$Release/cairn ./Cairnfo_V2
rm -f Cairn_Int
ln -s ./$Release/cairn ./Cairn_Int
cd /data/www/sites/$Release/cairn
ln -s /data/www/sites/cairn_includes/RSS rss
ln -s /data/www/sites/cairn_includes/vign_rev vign_rev
ln -s /data/www/sites/cairn_includes/wp wp
ln -s /data/www/sites/cairn_includes/docs docs
ln -s /data/www/sites/cairn_includes/OpenUrl OpenUrl
ln -s /data/www/sites/cairn_includes/xml flash/xml
ln -s /data/www/sites/sitemap static/sitemap
