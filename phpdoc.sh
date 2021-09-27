#!/bin/sh

docker pull phpdoc/phpdoc
docker run --rm -v $(pwd):/data phpdoc/phpdoc --ansi -d src -t doc --title sanitor
