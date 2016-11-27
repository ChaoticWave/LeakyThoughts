<?php
//******************************************************************************
//* Bootstrap
//******************************************************************************

$app = new Illuminate\Foundation\Application(realpath(__DIR__ . '/../'));

$app->singleton(Illuminate\Contracts\Http\Kernel::class, ChaoticWave\LeakyThoughts\Http\Kernel::class);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, ChaoticWave\LeakyThoughts\Console\Kernel::class);
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, ChaoticWave\LeakyThoughts\Exceptions\Handler::class);

return $app;
