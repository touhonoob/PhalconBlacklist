#!/usr/bin/env bash
pecl install redis-2.2.7
git clone -q --depth=1 https://github.com/phalcon/cphalcon.git -b $1
cd cphalcon/ext; export CFLAGS="-g3 -O1 -fno-delete-null-pointer-checks -Wall"; phpize && ./configure --enable-phalcon && make -j4 && make install