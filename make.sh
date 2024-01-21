#!/bin/bash -x
#
#
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail


BIN_SELF=$(readlink -f "$0")
APP_ROOT=$(dirname "$BIN_SELF")

f=$(readlink -f "$0")
d=$(dirname "$f")
cd "$d"

action="${1:-}"

case "$action" in
#
# Clean up the junk
clean)

	rm -frv ./node_modules
	rm -frv ./webroot/lib

	rm -fv ./webroot/css/app.css*
	rm -fv ./webroot/js/app.js webroot/js/app.js.gz
	rm -fv ./webroot/js/vendor.js webroot/js/vendor.js.gz

	;;

#
# Build the App CSS
css)

	./node_modules/.bin/sass \
		--no-quiet \
		--style=compressed \
		--verbose \
		scss/_.scss \
		webroot/css/app.css

	#_gzip ./webroot/css/app.css
	gzip --force --keep ./webroot/css/app.css

	;;

#
# Install the Dependencies
deps)

	#npm install jscpd
	#npm install postcss-cli autoprefixer cssnano
	#npm install bower

	npm update
	./node_modules/.bin/bower --allow-root update
	cd ./webroot/lib/jquery-ui && npm update && grunt concat

	;;

#
# Install
install)

	# jquery
	mkdir -p webroot/vendor/jquery/
	cp node_modules/jquery/dist/jquery.min.js webroot/vendor/jquery/
	cp node_modules/jquery/dist/jquery.min.map webroot/vendor/jquery/

	# jquery-ui
	mkdir -p webroot/vendor/jquery-ui/
	cp node_modules/jquery-ui/dist/jquery-ui.min.js webroot/vendor/jquery-ui/
	cp node_modules/jquery-ui/dist/themes/base/jquery-ui.min.css webroot/vendor/jquery-ui/

	# bootstrap
	mkdir -p webroot/vendor/bootstrap/
	cp node_modules/bootstrap/dist/css/bootstrap.min.css webroot/vendor/bootstrap/
	cp node_modules/bootstrap/dist/css/bootstrap.min.css.map webroot/vendor/bootstrap/
	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js webroot/vendor/bootstrap/
	cp node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map webroot/vendor/bootstrap/

	# font awesome
	mkdir -p webroot/vendor/fontawesome/css webroot/vendor/fontawesome/webfonts
	cp node_modules/@fortawesome/fontawesome-free/css/all.min.css webroot/vendor/fontawesome/css/
	cp node_modules/@fortawesome/fontawesome-free/webfonts/* webroot/vendor/fontawesome/webfonts/

	;;

#
# Build the JS STuff
js)

	rm -fr \
		./webroot/js/app.js \
		./webroot/js/app.js.gz \
		./webroot/js/vendor.js \
		./webroot/js/vendor.js.gz

	#cat ./webroot/lib/lodash/lodash.js \
	#	./webroot/lib/jquery/dist/jquery.js \
	#	./webroot/lib/jquery-ui/dist/jquery-ui.js \
	#	> ./webroot/js/vendor.js

	cat ./webroot/lib/lodash/lodash.js \
		./webroot/lib/jquery/dist/jquery.js \
		./webroot/lib/jquery-ui/ui/core.js \
		./webroot/lib/jquery-ui/ui/widget.js \
		./webroot/lib/jquery-ui/ui/position.js \
		./webroot/lib/jquery-ui/ui/autocomplete.js \
		./webroot/lib/jquery-ui/ui/menu.js \
		> ./webroot/js/vendor.js

	#
	# App CSS
	cat ./webroot/js/base.js \
		./webroot/js/weed.js \
		./webroot/js/weed-printer.js \
		./webroot/js/weed-bulkmenu.js \
		./webroot/js/weed-keyboard.js \
		./webroot/js/weed-scanner.js \
		> ./webroot/js/app.js

	#
	# Pack them
	./node_modules/.bin/uglifyjs ./webroot/js/vendor.js > ./webroot/js/vendor.js.tmp
	mv ./webroot/js/vendor.js.tmp ./webroot/js/vendor.js
	gzip --force --keep ./webroot/js/vendor.js

	./node_modules/.bin/uglifyjs ./webroot/js/app.js > ./webroot/js/app.js.tmp
	mv ./webroot/js/app.js.tmp ./webroot/js/app.js
	gzip --force --keep ./webroot/js/app.js

	;;

#
# Lint all the files
lint)

	jslint="./node_modules/.bin/jslint --maxerr=10 --terse"
	jslint="./node_modules/.bin/jshint --show-non-errors"

	#./node_modules/.bin/jscpd
	find_opts="! -path '*/node_modules/*' ! -path '*/vendor/*' ! -path '*webroot/lib*' -type f "
	find_opts="! -path './.git/*' ! -path './lib/php-google-cloud-print/*' ! -path './node_modules/*' ! -path './vendor/*' ! -path './webroot/lib/*'"

	# Lint PHP
	# find ./ $find_opts -name '*.php' -exec php -l {} \; >/dev/null

	# Lint JS
	#find \
	#	./jsx/ \
	#	./webroot/js \
	#	-name '*.js' \
	#	-exec $jslint {} \;


	# Lint Less
	# find ./webroot/css -name '*.less' -exec ./node_modules/.bin/lessc --lint {} \;

	# Lint CSS
	# find ./webroot/css $find_opts -name '*.css' -exec ./node_modules/.bin/csslint --format=compact --quiet {} \;

	#
	# Find Non Unix EOL Files
	#find ./ $find_opts -exec file {} \; \
	#		| grep CRLF \
	#		> CRLF.out

	#
	# Find File that have leading whitespace that is four or more spaces
	find ./ \
		! -path './.git/*' \
		! -path './lib/php-google-cloud-print/*' \
		! -path './node_modules/*' \
		! -path './vendor/*' \
		! -path './webroot/lib/*' \
		| xargs ack --nopager '^\t+ +'
	#	| grep -v ':0' \
	#		| awk 'BEGIN { FS = ":" }; { print $2 " " $1 }' \
	#	| xargs -l ack --count --nobreak --nofollow --nopager --sort-files --recurse '^\t+ +' \
	#		| sort -n \
	#		| tail -n 20

	;;

#
# Help, the default target
help|*)

	set +x

	echo
	echo "You must supply a make command"
	echo
	grep -ozP "^#\n#.*\n[a-zA-Z_-]+\)" $0 \
		| awk '/[a-zA-Z_-]+\)/ { printf " \033[0;49;31m%-15s\033[0m%s\n", $$1, gensub(/^# /, "", "", x) }; { x=$$0 }' \
		| sort
	echo

esac

# https://github.com/kucherenko/jscpd
#
# #
# # Make all the things for live
# live: css-full js-full
# 	#git clone https://github.com/yasirsiddiqui/php-google-cloud-print.git ./lib/php-google-cloud-print
# 	./composer.phar self-update
# 	./composer.phar update
