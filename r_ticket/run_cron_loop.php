<?php
set_time_limit(0);
ignore_user_abort(true);

while (true) {
    require __DIR__ . '/cron_runner.php';

    echo "[" . date('H:i:s') . "] cron executed\n";
    flush(); // ini sudah cukup di CLI

    sleep(2); // REKOMENDASI: 2–5 detik (jangan 1 detik)
}
