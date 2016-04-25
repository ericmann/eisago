#!/usr/bin/env sh
for i in {1..10}; do time ./eisago.sh import --mode=concurrent --quiet; done 2>&1 | grep ^real | sed -e s/.*m// | awk '{sum += $1} END {print sum / NR}'
