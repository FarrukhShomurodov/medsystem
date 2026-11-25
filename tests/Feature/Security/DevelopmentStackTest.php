<?php

/*
| compose.yaml, the local development stack. Its database role is a superuser with a published
| default password and the demo accounts have one too, so by default nothing the stack publishes
| is reachable from the network (FORWARD_HOST_IP opens it on purpose).
*/

it('publishes every port of the development stack on the loopback address only', function (): void {
    $compose = (string) file_get_contents(base_path('compose.yaml'));

    preg_match_all('/^\h*ports:\h*\R((?:\h*-\h*\S.*\R?)+)/m', $compose, $blocks);
    preg_match_all('/^\h*-\h*(\S.*?)\h*$/m', implode("\n", $blocks[1]), $ports);

    expect($ports[1])->not->toBeEmpty()
        ->each->toStartWith('"${FORWARD_HOST_IP:-127.0.0.1}:');
});
