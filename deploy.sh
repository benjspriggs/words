#!/bin/sh
# update the source from origin

git reset --hard
git clean -f
git pull origin master
