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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // percentage, fixed
            $table->decimal('value', 10, 2); // قيمة الخصم (نسبة مئوية أو قيمة ثابتة)
            $table->decimal('min_order_amount', 10, 2)->default(0); // الحد الأدنى لقيمة الطلب
            $table->integer('max_uses')->nullable(); // الحد الأقصى لعدد مرات الاستخدام
            $table->integer('used_times')->default(0); // عدد مرات الاستخدام الحالية
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
}; 