#!/bin/bash

rm -rf dist
mkdir dist 
cp -r app bootstrap config routes  database resources storage dist
cp composer.json postcss.config.js  artisan dist
npm run build
cp -r public dist

cd dist/public
rm image storage
ln -s ../storage/app/public storage
ln -s ../storage/app/images image

cd ..
composer install --no-dev


               