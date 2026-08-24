<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\TwilioSmsService;
use Illuminate\Console\Command;

class SendPickupReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:send-pickup-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send SMS pickup reminders for reservations scheduled for tomorrow';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $reservations = Reservation::where('status', Reservation::STATUS_RESERVED)
            ->where('approved_pickup_date', $tomorrow)
            ->with(['user', 'food'])
            ->get();

        if ($reservations->isEmpty()) {
            $this->info('No pickup reminders to send for tomorrow.');
            return Command::SUCCESS;
        }

        $smsService = app(TwilioSmsService::class);
        $sent = 0;

        foreach ($reservations as $reservation) {
            try {
                $smsService->sendPickupReminder($reservation);
                $sent++;
                $this->line("Reminder sent to {$reservation->user->name} for \"{$reservation->food->food_name}\".");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for reservation #{$reservation->id}: {$e->getMessage()}");
            }
        }

        $this->info("Pickup reminders sent: {$sent}/{$reservations->count()}.");

        return Command::SUCCESS;
    }
}
