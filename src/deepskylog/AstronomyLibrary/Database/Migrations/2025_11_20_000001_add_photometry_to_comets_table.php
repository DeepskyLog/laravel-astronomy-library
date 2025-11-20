<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotometryToCometsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comets_orbital_elements', function (Blueprint $table) {
            $table->float('H')->nullable()->after('Tp');
            $table->float('n')->nullable()->after('H');
            $table->float('phase_coeff')->nullable()->after('n');
            $table->float('n_pre')->nullable()->after('phase_coeff');
            $table->float('n_post')->nullable()->after('n_pre');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comets_orbital_elements', function (Blueprint $table) {
            $table->dropColumn(['H', 'n', 'phase_coeff', 'n_pre', 'n_post']);
        });
    }
}
