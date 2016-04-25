#!/usr/bin/env sh
cmd="./eisago.sh import --mode=parallel --porcelain"; time for i in {1..10}; do $cmd; done