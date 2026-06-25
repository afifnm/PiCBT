<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$parser = new App\Services\QuestionTxtParser();
$raw = "SOAL: Pertanyaan 1
A. <img>
B. <a>*
C. <link>
D. <href>
";

print_r($parser->parse($raw));
