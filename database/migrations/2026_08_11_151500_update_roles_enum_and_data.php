<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Si PostgreSQL utilise des contraintes check ou des types enum
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        }

        // Convertir les utilisateurs SD et DIR en ADMIN
        DB::table('users')->whereIn('role', ['SD', 'DIR'])->update(['role' => 'ADMIN']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['ADMIN'::text, 'CDD'::text, 'CDS'::text, 'CDB'::text, 'ADS'::text]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        }

        DB::table('users')->where('role', 'ADMIN')->update(['role' => 'SD']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['SD'::text, 'CDD'::text, 'CDS'::text, 'CDB'::text, 'ADS'::text, 'DIR'::text]))");
        }
    }
};
