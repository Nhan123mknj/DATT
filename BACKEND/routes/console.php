<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:process-due-reservations')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();


Schedule::command('reservations:cancel-pending', ['--hours' => 24])
    ->hourly()
    ->timezone('Asia/Ho_Chi_Minh')
    ->name('Auto-cancel pending reservations')
    ->onSuccess(function () {
        \Log::info('✅ Auto-cancel command completed');
    })
    ->onFailure(function () {
        \Log::error('❌ Auto-cancel command failed');
    });

Schedule::command('borrows:update-overdue')
    ->daily()
    ->timezone('Asia/Ho_Chi_Minh')
    ->withoutOverlapping();

Schedule::command('app:notify-upcoming-return')
    ->dailyAt('08:00')
    ->timezone('Asia/Ho_Chi_Minh')
    ->withoutOverlapping();

Schedule::command('borrows:cancel-abandoned')
    ->dailyAt('23:00')
    ->timezone('Asia/Ho_Chi_Minh')
    ->withoutOverlapping();
