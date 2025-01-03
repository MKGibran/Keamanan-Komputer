<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_name');
            $table->string('file_path');
            $table->text('aes_key'); // Kunci AES yang terenkripsi RSA
            $table->integer('file_size')->nullable(); // Opsional: Ukuran file
            $table->string('mime_type')->nullable(); // Opsional: MIME type
            $table->unsignedBigInteger('uploaded_by')->nullable(); // Opsional: User ID
            $table->timestamps(); // created_at dan updated_at
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
