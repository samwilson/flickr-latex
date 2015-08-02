#!/bin/bash

for ALBUM in data/albums/*; do
    cd $ALBUM
    #TEX=$ALBUM/album.tex
    #BASE=$(basename $TEX ".tex")
    #DIR=$(dirname $TEX)
    pdflatex album
    makeindex album
    pdflatex album
    pdflatex album
done


#pdflatex main
#bibtex main
#makeindex main
#pdflatex main
#pdflatex main
