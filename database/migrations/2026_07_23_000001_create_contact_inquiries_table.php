<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('company', 200)->nullable();
            $table->string('email', 255);
            $table->string('contact', 30);
            $table->text('message');
            $table->boolean('admin_notified')->default(false);
            $table->boolean('client_notified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
