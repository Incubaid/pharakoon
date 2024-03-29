#!/bin/bash -xue

USER=`whoami`

cd src/client/php/test

export PATH=/opt/qbase3/bin/:/opt/qbase3/apps/arakoon/bin:$PATH
export LD_LIBRARY_PATH=/lib:/lib/x86_64-linux-gnu:/opt/qbase3/lib64

php all_nodes_down_test.php
php all_nodes_up_test.php
php invalid_client_configuration_test.php
php master_failover_test.php
php one_node_down_test.php
#php stats_test.php

sudo chown $USER *.xml

mkdir -p ${WORKSPACE}/phpresults
mv *.xml ${WORKSPACE}/phpresults
