<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// مسار اختبار لعرض الصور
Route::get('/test-images', function () {
    // الحصول على جميع الملفات في مجلد المنتجات
    $files = Storage::disk('public')->files('products');
    
    echo '<h1>اختبار الصور</h1>';
    
    if (empty($files)) {
        echo '<p>لا توجد صور في مجلد المنتجات.</p>';
    } else {
        echo '<ul>';
        foreach ($files as $file) {
            $url = Storage::url($file);
            echo '<li>';
            echo '<p>مسار الملف: ' . $file . '</p>';
            echo '<p>رابط الصورة: ' . $url . '</p>';
            echo '<img src="' . $url . '" style="max-width: 300px;">';
            echo '</li>';
        }
        echo '</ul>';
    }
});

// مسار لتحميل صورة اختبارية
Route::get('/upload-test-image', function () {
    // إنشاء مجلد المنتجات إذا لم يكن موجوداً
    if (!Storage::disk('public')->exists('products')) {
        Storage::disk('public')->makeDirectory('products');
    }
    
    // إنشاء ملف اختباري
    $content = 'هذا ملف اختباري';
    $path = 'products/test-image.txt';
    Storage::disk('public')->put($path, $content);
    
    return 'تم تحميل ملف اختباري بنجاح: ' . Storage::url($path);
});
