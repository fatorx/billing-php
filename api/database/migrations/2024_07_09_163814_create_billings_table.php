<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->string('government_id', 11);
            $table->string('email', 100);
            $table->string('name', 200);
            $table->decimal('amount', 10, 2);
            $table->timestamp('due_date');
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
}
