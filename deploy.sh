#!/bin/sh
# update the source from origin

whoami
git reset --hard
git clean -f
git pull origin master
