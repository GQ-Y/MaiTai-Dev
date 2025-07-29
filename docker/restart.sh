#!/bin/bash
ps -ef | grep -v grep | grep MineAdmin | awk '{print $2}'|xargs -t -r kill -15
rm -rf runtime/container
# Use the swoole-cli from the project root directory
nohup ./swoole-cli -d swoole.use_shortname='Off' bin/hyperf.php start >/dev/null 2>&1 &