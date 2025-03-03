#!/bin/bash

set -e

VNU_JAR_PATH=utils/vnu.jar

# Start v.Nu HTTP server
if [ ! -f "$VNU_JAR_PATH" ]; then
  echo "vnu.jar missing at $VNU_JAR_PATH. Please place it in that location."
  exit 1
fi
if nc -z localhost 8888; then
  echo "v.Nu (Nu Html Checker) is already up and running at http://localhost:8888"
else
  java -cp "$VNU_JAR_PATH" nu.validator.servlet.Main 8888 &
  while ! nc -z localhost 8888; do
    sleep 1
    echo .
  done
  echo "v.Nu (Nu Html Checker) is now up and running at http://localhost:8888"
fi
