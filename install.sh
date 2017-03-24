#!/bin/sh
# setup groups and permissions
# to allow for deploy

set -eu
IFS='\n\t'

group="deploy-`date +%s`"

echo "Creating group '$group'"

# create deploy group
groupadd $group

(
users=`ps aux \
  | egrep "(apache|httpd)" \
  | grep -v "root" \
  | grep -v "grep" \
  | grep -o "^[A-Za-z-]*" \
  | uniq`

echo "Adding users: "
echo $users

IFS='
'
for user in $users; do usermod -G $group $user; done
)

# create and add deploy group to app.config.yml
if [ ! -e "app.config.yml" ]
then
cat << EOF > app.config.yml
---
deploy:
EOF
fi

echo "group: $group" >> app.config.yml
