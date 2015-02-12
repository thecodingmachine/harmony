#!/bin/bash

IFS="
"

olddir=`pwd`
dir=
        for i in $( ls -1 vendor/mouf/); do
                cd $olddir/vendor/mouf/$i

		echo "Running git $@ on $i"
		git $@
        done

cd $olddir

olddir=`pwd`
dir=
        for i in $( ls -1 vendor/framework-interop/); do
                cd $olddir/vendor/framework-interop/$i

		echo "Running git $@ on $i"
		git $@
        done

cd $olddir
