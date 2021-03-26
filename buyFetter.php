<?php

$Slaves = new app(
    'Bearer .....',
    't.me/vyxelfan'
);

while (true) {
    $Slaves->getSlavesWithoutFetter(function ($slave_id) use ($Slaves) {
        sleep(mt_rand(1, 2));
        $Slaves->buyFetter($slave_id);
    });
}
