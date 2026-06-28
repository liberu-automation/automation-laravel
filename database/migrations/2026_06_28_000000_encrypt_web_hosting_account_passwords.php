<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Encrypt pre-existing plaintext passwords now that the model casts
     * `password` to `encrypted`. Raw DB access bypasses the model cast.
     * Idempotent: rows that already decrypt are left untouched, so this
     * is safe to re-run.
     */
    public function up(): void
    {
        DB::table('web_hosting_accounts')
            ->select('id', 'password')
            ->whereNotNull('password')
            ->cursor()
            ->each(function ($row) {
                try {
                    Crypt::decryptString($row->password);

                    return; // already encrypted
                } catch (DecryptException) {
                    // plaintext — encrypt below
                }

                DB::table('web_hosting_accounts')
                    ->where('id', $row->id)
                    ->update(['password' => Crypt::encryptString($row->password)]);
            });
    }

    /**
     * No down(): decrypting back to plaintext would re-introduce the
     * vulnerability this migration fixes.
     */
    public function down(): void
    {
        // intentionally irreversible
    }
};
