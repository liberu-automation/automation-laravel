<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_hosting_accounts', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('web_hosting_accounts', function (Blueprint $table) {
            $table->dropIndex(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};
