# توثيق واجهة برمجة التطبيقات (API) لنظام إدارة صور المنتجات

هذا المستند يوفر توثيقًا شاملاً لنقاط نهاية API المتعلقة بنظام إدارة صور المنتجات في التطبيق. يتضمن معلومات حول كيفية استخدام كل نقطة نهاية، والمتطلبات، والاستجابات المتوقعة.

## جدول المحتويات

1. [نظرة عامة](#نظرة-عامة)
2. [عرض صور المنتج](#عرض-صور-المنتج)
3. [تحميل صور المنتج](#تحميل-صور-المنتج)
   - [تحميل صورة واحدة](#تحميل-صورة-واحدة)
   - [تحميل صور متعددة](#تحميل-صور-متعددة)
4. [إدارة صور المنتج](#إدارة-صور-المنتج)
   - [تعيين الصورة الرئيسية](#تعيين-الصورة-الرئيسية)
   - [تحديث ترتيب الصور](#تحديث-ترتيب-الصور)
   - [حذف صورة منتج](#حذف-صورة-منتج)
5. [هيكل البيانات](#هيكل-البيانات)
   - [نموذج صورة المنتج](#نموذج-صورة-المنتج)
   - [العلاقات](#العلاقات)

## نظرة عامة

نظام إدارة صور المنتجات يتيح للمستخدمين والمشرفين التعامل مع صور المنتجات بالطرق التالية:

- عرض صور منتج معين
- تحميل صورة واحدة أو عدة صور للمنتج
- تعيين صورة رئيسية للمنتج
- تحديث ترتيب عرض الصور
- حذف صور المنتج

يمكن للمستخدمين العاديين عرض صور المنتجات فقط، بينما يمكن للمشرفين إدارة الصور بالكامل (إضافة، تعديل، حذف).

## عرض صور المنتج

يعرض جميع الصور المرتبطة بمنتج معين.

- **URL**: `/api/products/{productId}/images`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مستخدم عادي أو مشرف
- **معلمات المسار**:
  - `productId`: معرف المنتج (مطلوب، عدد صحيح)

### استجابة ناجحة (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "product_id": 5,
      "image_path": "products/battery_1.jpg",
      "image_url": "http://example.com/storage/products/battery_1.jpg",
      "is_primary": true,
      "sort_order": 0,
      "created_at": "2023-06-15T10:30:00.000000Z",
      "updated_at": "2023-06-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "product_id": 5,
      "image_path": "products/battery_2.jpg",
      "image_url": "http://example.com/storage/products/battery_2.jpg",
      "is_primary": false,
      "sort_order": 1,
      "created_at": "2023-06-15T10:35:00.000000Z",
      "updated_at": "2023-06-15T10:35:00.000000Z"
    }
  ]
}
```

### استجابة الخطأ (404 Not Found)

```json
{
  "message": "المنتج غير موجود."
}
```

## تحميل صور المنتج

### تحميل صورة واحدة

يقوم بتحميل صورة واحدة للمنتج.

- **URL**: `/api/products/{productId}/images`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `productId`: معرف المنتج (مطلوب، عدد صحيح)
- **معلمات الطلب**:
  - `image`: ملف الصورة (مطلوب، صورة، الحد الأقصى 2048 كيلوبايت)
  - `is_primary`: تعيين كصورة رئيسية (اختياري، قيمة منطقية، افتراضيًا false)
  - `sort_order`: ترتيب الصورة (اختياري، عدد صحيح)

#### استجابة ناجحة (200 OK)

```json
{
  "id": 3,
  "product_id": 5,
  "image_path": "products/battery_3.jpg",
  "image_url": "http://example.com/storage/products/battery_3.jpg",
  "is_primary": true,
  "sort_order": 2,
  "created_at": "2023-06-15T11:30:00.000000Z",
  "updated_at": "2023-06-15T11:30:00.000000Z"
}
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "errors": {
    "image": [
      "يجب أن يكون الملف صورة.",
      "يجب ألا يتجاوز حجم الصورة 2048 كيلوبايت."
    ]
  }
}
```

### تحميل صور متعددة

يقوم بتحميل عدة صور للمنتج في وقت واحد.

- **URL**: `/api/products/{productId}/images/multiple`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `productId`: معرف المنتج (مطلوب، عدد صحيح)
- **معلمات الطلب**:
  - `images[]`: مصفوفة من ملفات الصور (مطلوب، صور، الحد الأقصى 2048 كيلوبايت لكل صورة)

#### استجابة ناجحة (200 OK)

```json
[
  {
    "id": 4,
    "product_id": 5,
    "image_path": "products/battery_4.jpg",
    "image_url": "http://example.com/storage/products/battery_4.jpg",
    "is_primary": false,
    "sort_order": 3,
    "created_at": "2023-06-15T12:30:00.000000Z",
    "updated_at": "2023-06-15T12:30:00.000000Z"
  },
  {
    "id": 5,
    "product_id": 5,
    "image_path": "products/battery_5.jpg",
    "image_url": "http://example.com/storage/products/battery_5.jpg",
    "is_primary": false,
    "sort_order": 4,
    "created_at": "2023-06-15T12:30:00.000000Z",
    "updated_at": "2023-06-15T12:30:00.000000Z"
  }
]
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "errors": {
    "images": [
      "يجب توفير مصفوفة من الصور."
    ]
  }
}
```

## إدارة صور المنتج

### تعيين الصورة الرئيسية

يقوم بتعيين صورة معينة كصورة رئيسية للمنتج.

- **URL**: `/api/products/{productId}/images/{imageId}/set-primary`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `productId`: معرف المنتج (مطلوب، عدد صحيح)
  - `imageId`: معرف الصورة (مطلوب، عدد صحيح)

#### استجابة ناجحة (200 OK)

```json
{
  "id": 2,
  "product_id": 5,
  "image_path": "products/battery_2.jpg",
  "image_url": "http://example.com/storage/products/battery_2.jpg",
  "is_primary": true,
  "sort_order": 1,
  "created_at": "2023-06-15T10:35:00.000000Z",
  "updated_at": "2023-06-15T12:45:00.000000Z"
}
```

#### استجابة الخطأ (404 Not Found)

```json
{
  "message": "الصورة غير موجودة."
}
```

### تحديث ترتيب الصور

يقوم بتحديث ترتيب عرض صور المنتج.

- **URL**: `/api/products/{productId}/images/order`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `productId`: معرف المنتج (مطلوب، عدد صحيح)
- **معلمات الطلب**:
  - `images_order`: مصفوفة من معرفات الصور مرتبة حسب الترتيب المطلوب (مطلوب، مصفوفة)

#### استجابة ناجحة (200 OK)

```json
[
  {
    "id": 1,
    "product_id": 5,
    "image_path": "products/battery_1.jpg",
    "image_url": "http://example.com/storage/products/battery_1.jpg",
    "is_primary": true,
    "sort_order": 0,
    "created_at": "2023-06-15T10:30:00.000000Z",
    "updated_at": "2023-06-15T13:00:00.000000Z"
  },
  {
    "id": 3,
    "product_id": 5,
    "image_path": "products/battery_3.jpg",
    "image_url": "http://example.com/storage/products/battery_3.jpg",
    "is_primary": false,
    "sort_order": 1,
    "created_at": "2023-06-15T11:30:00.000000Z",
    "updated_at": "2023-06-15T13:00:00.000000Z"
  },
  {
    "id": 2,
    "product_id": 5,
    "image_path": "products/battery_2.jpg",
    "image_url": "http://example.com/storage/products/battery_2.jpg",
    "is_primary": false,
    "sort_order": 2,
    "created_at": "2023-06-15T10:35:00.000000Z",
    "updated_at": "2023-06-15T13:00:00.000000Z"
  }
]
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "errors": {
    "images_order": [
      "يجب توفير مصفوفة من معرفات الصور."
    ]
  }
}
```

### حذف صورة منتج

يقوم بحذف صورة معينة من المنتج.

- **URL**: `/api/products/{productId}/images/{imageId}`
- **Method**: `DELETE`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `productId`: معرف المنتج (مطلوب، عدد صحيح)
  - `imageId`: معرف الصورة (مطلوب، عدد صحيح)

#### استجابة ناجحة (200 OK)

```json
{
  "message": "تم حذف الصورة بنجاح"
}
```

#### استجابة الخطأ (404 Not Found)

```json
{
  "message": "الصورة غير موجودة."
}
```

## هيكل البيانات

### نموذج صورة المنتج

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'sort_order',
    ];

    /**
     * العلاقة مع المنتج
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
```

### العلاقات

#### علاقة المنتج بالصور

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // ... الكود الموجود

    /**
     * العلاقة مع صور المنتج
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * الحصول على الصورة الرئيسية للمنتج
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }
}
```

## ملاحظات هامة

1. يتم تخزين الصور في مجلد `storage/app/public/products` ويمكن الوصول إليها عبر الرابط `storage/products`.
2. يجب تنفيذ الأمر `php artisan storage:link` لإنشاء رابط رمزي للتخزين العام.
3. عند حذف منتج، يتم حذف جميع الصور المرتبطة به تلقائيًا (باستخدام `onDelete('cascade')` في المهاجرة).
4. يتم تعيين أول صورة يتم تحميلها للمنتج كصورة رئيسية تلقائيًا.
5. يمكن تحميل الصور بتنسيقات JPEG، PNG، JPG، و GIF فقط، والحد الأقصى لحجم الصورة هو 2 ميجابايت.
6. عند حذف صورة رئيسية، يتم تعيين أول صورة متبقية كصورة رئيسية جديدة تلقائيًا.
7. يمكن للمستخدمين العاديين عرض صور المنتجات فقط، بينما يمكن للمشرفين إدارة الصور بالكامل.