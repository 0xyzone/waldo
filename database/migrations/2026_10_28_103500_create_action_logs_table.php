<?php

use App\Models\IdCardRequest;
use App\Models\UnofficialNametagRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('action_logs')) {
            Schema::create('action_logs', function (Blueprint $table) {
                $table->id();
                $table->string('actionable_type')->index();
                $table->string('actionable_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name')->nullable();
                $table->string('action')->default('updated')->index();
                $table->string('from_status')->nullable();
                $table->string('to_status')->nullable();
                $table->text('action_description')->nullable();
                $table->json('changes')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['actionable_type', 'actionable_id', 'created_at'], 'actionable_composite_idx');
            });

            // Backfill existing logs from IdCardRequest and UnofficialNametagRequest
            if (Schema::hasTable('id_card_request_logs')) {
                $existingIdLogs = DB::table('id_card_request_logs')->get();
                $payload = [];
                foreach ($existingIdLogs as $item) {
                    $payload[] = [
                        'actionable_type' => IdCardRequest::class,
                        'actionable_id' => (string) $item->id_card_request_id,
                        'user_id' => $item->user_id,
                        'user_name' => $item->user_name,
                        'action' => $item->from_status ? 'status_changed' : 'created',
                        'from_status' => $item->from_status,
                        'to_status' => $item->to_status,
                        'action_description' => $item->action_description,
                        'changes' => null,
                        'created_at' => $item->created_at,
                    ];
                }
                if (! empty($payload)) {
                    DB::table('action_logs')->insert($payload);
                }
            }

            if (Schema::hasTable('unofficial_nametag_request_logs')) {
                $existingNametagLogs = DB::table('unofficial_nametag_request_logs')->get();
                $payload = [];
                foreach ($existingNametagLogs as $item) {
                    $payload[] = [
                        'actionable_type' => UnofficialNametagRequest::class,
                        'actionable_id' => (string) $item->unofficial_nametag_request_id,
                        'user_id' => $item->user_id,
                        'user_name' => $item->user_name,
                        'action' => $item->from_status ? 'status_changed' : 'created',
                        'from_status' => $item->from_status,
                        'to_status' => $item->to_status,
                        'action_description' => $item->action_description,
                        'changes' => null,
                        'created_at' => $item->created_at,
                    ];
                }
                if (! empty($payload)) {
                    DB::table('action_logs')->insert($payload);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
