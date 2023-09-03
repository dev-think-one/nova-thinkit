<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('contacts_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()
                ->constrained('contacts', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contacts_meta');
    }
};
