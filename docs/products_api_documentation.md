# توثيق واجهة برمجة التطبيقات (API) لنظام إدارة المنتجات

هذا المستند يوفر توثيقًا شاملاً لنقاط نهاية API المتعلقة بنظام إدارة المنتجات في التطبيق. يتضمن معلومات حول كيفية استخدام كل نقطة نهاية، والمتطلبات، والاستجابات المتوقعة.

## جدول المحتويات

1. [نظرة عامة](#نظرة-عامة)
2. [عرض المنتجات](#عرض-المنتجات)
   - [قائمة المنتجات](#قائمة-المنتجات)
   - [عرض منتج محدد](#عرض-منتج-محدد)
3. [إدارة المنتجات](#إدارة-المنتجات)
   - [إنشاء منتج جديد](#إنشاء-منتج-جديد)
   - [تحديث منتج](#تحديث-منتج)
   - [حذف منتج](#حذف-منتج)
   - [تبديل حالة المنتج](#تبديل-حالة-المنتج)
4. [أنواع المنتجات](#أنواع-المنتجات)
   - [البطاريات](#البطاريات)
   - [ألواح الطاقة الشمسية](#ألواح-الطاقة-الشمسية)
   - [الإنفيرتر](#الإنفيرتر)
5. [هيكل البيانات](#هيكل-البيانات)
   - [نموذج المنتج](#نموذج-المنتج)

## نظرة عامة

نظام إدارة المنتجات يتيح للمستخدمين والمشرفين التعامل مع المنتجات بالطرق التالية:

- عرض قائمة المنتجات مع إمكانية التصفية والترتيب
- عرض تفاصيل منتج محدد
- إنشاء منتجات جديدة (للمشرفين فقط)
- تحديث بيانات المنتجات (للمشرفين فقط)
- حذف المنتجات (للمشرفين فقط)
- تبديل حالة المنتجات بين نشط وغير نشط (للمشرفين فقط)

يدعم النظام ثلاثة أنواع من المنتجات:
- البطاريات
- ألواح الطاقة الشمسية
- الإنفيرتر (محولات الطاقة)

## عرض المنتجات

### قائمة المنتجات

يعرض قائمة المنتجات مع إمكانية التصفية والترتيب.

- **URL**: `/api/products`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مستخدم عادي أو مشرف
- **معلمات الاستعلام**:
  - `type`: تصفية حسب نوع المنتج (`battery`, `solar_panel`, `inverter`) (اختياري)
  - `status`: تصفية حسب حالة المنتج (`active`, `inactive`) (اختياري)
  - `sort_by`: الحقل المراد الترتيب حسبه (اختياري، افتراضيًا `created_at`)
  - `sort_direction`: اتجاه الترتيب (`asc`, `desc`) (اختياري، افتراضيًا `desc`)
  - `per_page`: عدد العناصر في الصفحة (اختياري، افتراضيًا 15)
  - `page`: رقم الصفحة (اختياري، افتراضيًا 1)
  - `with_images`: تضمين صور المنتجات (`true`, `false`) (اختياري، افتراضيًا `false`)
  - `with_primary_image`: تضمين الصورة الرئيسية للمنتج (`true`, `false`) (اختياري، افتراضيًا `true`)

#### استجابة ناجحة (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "name": "بطارية ليثيوم أيون 12 فولت",
      "description": "بطارية ليثيوم أيون عالية الجودة بقدرة 100 أمبير",
      "price": 1500.00,
      "quantity": 25,
      "type": "battery",
      "type_label": "بطارية",
      "specifications": {
        "capacity": 100,
        "voltage": 12,
        "chemistry": "Lithium-Ion",
        "cycle_life": 2000,
        "dimensions": "30x20x15 cm",
        "weight": 10.5,
        "brand": "PowerTech"
      },
      "status": "active",
      "status_label": "نشط",
      "primary_image": {
        "id": 1,
        "product_id": 1,
        "image_path": "products/battery_1.jpg",
        "image_url": "http://example.com/storage/products/battery_1.jpg",
        "is_primary": true,
        "sort_order": 0,
        "created_at": "2023-06-15T10:30:00.000000Z",
        "updated_at": "2023-06-15T10:30:00.000000Z"
      },
      "created_at": "2023-06-15T10:30:00.000000Z",
      "updated_at": "2023-06-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "name": "لوح طاقة شمسية 300 واط",
      "description": "لوح طاقة شمسية أحادي البلورية بقدرة 300 واط",
      "price": 800.00,
      "quantity": 50,
      "type": "solar_panel",
      "type_label": "لوح طاقة شمسية",
      "specifications": {
        "power": 300,
        "voltage": 24.5,
        "current": 8.2,
        "dimensions": "165x99x4 cm",
        "weight": 18.5,
        "cell_type": "Monocrystalline",
        "efficiency": 19.5,
        "brand": "SolarMax"
      },
      "status": "active",
      "status_label": "نشط",
      "primary_image": {
        "id": 3,
        "product_id": 2,
        "image_path": "products/solar_panel_1.jpg",
        "image_url": "http://example.com/storage/products/solar_panel_1.jpg",
        "is_primary": true,
        "sort_order": 0,
        "created_at": "2023-06-15T10:30:00.000000Z",
        "updated_at": "2023-06-15T10:30:00.000000Z"
      },
      "created_at": "2023-06-15T10:30:00.000000Z",
      "updated_at": "2023-06-15T10:30:00.000000Z"
    }
  ],
  "meta": {
    "total_products": 30,
    "current_page": 1,
    "per_page": 15,
    "last_page": 2,
    "from": 1,
    "to": 15
  }
}
```

### عرض منتج محدد

يعرض تفاصيل منتج محدد.

- **URL**: `/api/products/{id}`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مستخدم عادي أو مشرف
- **معلمات المسار**:
  - `id`: معرف المنتج (مطلوب، عدد صحيح)

#### استجابة ناجحة (200 OK)

```json
{
  "id": 1,
  "name": "بطارية ليثيوم أيون 12 فولت",
  "description": "بطارية ليثيوم أيون عالية الجودة بقدرة 100 أمبير",
  "price": 1500.00,
  "quantity": 25,
  "type": "battery",
  "type_label": "بطارية",
  "specifications": {
    "capacity": 100,
    "voltage": 12,
    "chemistry": "Lithium-Ion",
    "cycle_life": 2000,
    "dimensions": "30x20x15 cm",
    "weight": 10.5,
    "brand": "PowerTech"
  },
  "status": "active",
  "status_label": "نشط",
  "primary_image": {
    "id": 1,
    "product_id": 1,
    "image_path": "products/battery_1.jpg",
    "image_url": "http://example.com/storage/products/battery_1.jpg",
    "is_primary": true,
    "sort_order": 0,
    "created_at": "2023-06-15T10:30:00.000000Z",
    "updated_at": "2023-06-15T10:30:00.000000Z"
  },
  "images": [
    {
      "id": 1,
      "product_id": 1,
      "image_path": "products/battery_1.jpg",
      "image_url": "http://example.com/storage/products/battery_1.jpg",
      "is_primary": true,
      "sort_order": 0,
      "created_at": "2023-06-15T10:30:00.000000Z",
      "updated_at": "2023-06-15T10:30:00.000000Z"
    },
    {
      "id": 2,
      "product_id": 1,
      "image_path": "products/battery_2.jpg",
      "image_url": "http://example.com/storage/products/battery_2.jpg",
      "is_primary": false,
      "sort_order": 1,
      "created_at": "2023-06-15T10:35:00.000000Z",
      "updated_at": "2023-06-15T10:35:00.000000Z"
    }
  ],
  "created_at": "2023-06-15T10:30:00.000000Z",
  "updated_at": "2023-06-15T10:30:00.000000Z"
}
```

#### استجابة الخطأ (404 Not Found)

```json
{
  "message": "المنتج غير موجود."
}
```

## إدارة المنتجات

### إنشاء منتج جديد

ينشئ منتج جديد في النظام.

- **URL**: `/api/products`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات الطلب**:
  - `name`: اسم المنتج (مطلوب، نص، الحد الأقصى 255 حرف)
  - `description`: وصف المنتج (اختياري، نص)
  - `price`: سعر المنتج (مطلوب، رقم عشري موجب)
  - `quantity`: الكمية المتاحة (مطلوب، عدد صحيح موجب)
  - `type`: نوع المنتج (مطلوب، واحد من: `battery`, `solar_panel`, `inverter`)
  - `specifications`: مواصفات المنتج (مطلوب، مصفوفة تختلف حسب نوع المنتج)
  - `status`: حالة المنتج (اختياري، واحد من: `active`, `inactive`، افتراضيًا `active`)

#### مواصفات البطاريات المطلوبة:
  - `capacity`: سعة البطارية (مطلوب، رقم موجب)
  - `voltage`: الجهد (مطلوب، رقم موجب)
  - `chemistry`: نوع الكيمياء (مطلوب، نص)
  - `cycle_life`: عمر الدورة (اختياري، عدد صحيح موجب)
  - `dimensions`: الأبعاد (اختياري، نص)
  - `weight`: الوزن (اختياري، رقم موجب)
  - `brand`: العلامة التجارية (اختياري، نص)

#### مواصفات ألواح الطاقة الشمسية المطلوبة:
  - `power`: القدرة (مطلوب، رقم موجب)
  - `voltage`: الجهد (مطلوب، رقم موجب)
  - `current`: التيار (مطلوب، رقم موجب)
  - `dimensions`: الأبعاد (اختياري، نص)
  - `weight`: الوزن (اختياري، رقم موجب)
  - `cell_type`: نوع الخلية (اختياري، نص)
  - `efficiency`: الكفاءة (اختياري، رقم بين 0 و 100)
  - `brand`: العلامة التجارية (اختياري، نص)

#### مواصفات الإنفيرتر المطلوبة:
  - `power`: القدرة (مطلوب، رقم موجب)
  - `input_voltage`: جهد الدخل (مطلوب، رقم موجب)
  - `output_voltage`: جهد الخرج (مطلوب، رقم موجب)
  - `efficiency`: الكفاءة (اختياري، رقم بين 0 و 100)
  - `dimensions`: الأبعاد (اختياري، نص)
  - `weight`: الوزن (اختياري، رقم موجب)
  - `type`: النوع (اختياري، نص)
  - `brand`: العلامة التجارية (اختياري، نص)

#### استجابة ناجحة (201 Created)

```json
{
  "id": 3,
  "name": "انفيرتر 2000 واط",
  "description": "انفيرتر نقي الموجة بقدرة 2000 واط",
  "price": 2500.00,
  "quantity": 15,
  "type": "inverter",
  "type_label": "انفيرتر",
  "specifications": {
    "power": 2000,
    "input_voltage": 24,
    "output_voltage": 220,
    "efficiency": 95.5,
    "dimensions": "40x30x15 cm",
    "weight": 12.5,
    "type": "Pure Sine Wave",
    "brand": "InverTech"
  },
  "status": "active",
  "status_label": "نشط",
  "created_at": "2023-06-15T14:30:00.000000Z",
  "updated_at": "2023-06-15T14:30:00.000000Z"
}
```

#### استجابة الخطأ (422 Unprocessable Entity)

```json
{
  "errors": {
    "name": [
      "اسم المنتج مطلوب."
    ],
    "price": [
      "يجب أن يكون السعر رقمًا موجبًا."
    ],
    "specifications.power": [
      "قدرة الإنفيرتر مطلوبة."
    ]
  }
}
```

### تحديث منتج

يقوم بتحديث بيانات منتج موجود.

- **URL**: `/api/products/{id}`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `id`: معرف المنتج (مطلوب، عدد صحيح)
- **معلمات الطلب**:
  - نفس معلمات إنشاء المنتج، لكنها جميعًا اختيارية

#### استجابة ناجحة (200 OK)

```json
{
  "id": 3,
  "name": "انفيرتر 2000 واط - محدث",
  "description": "انفيرتر نقي الموجة بقدرة 2000 واط مع حماية إضافية",
  "price": 2700.00,
  "quantity": 20,
  "type": "inverter",
  "type_label": "انفيرتر",
  "specifications": {
    "power": 2000,
    "input_voltage": 24,
    "output_voltage": 220,
    "efficiency": 96.5,
    "dimensions": "40x30x15 cm",
    "weight": 12.5,
    "type": "Pure Sine Wave",
    "brand": "InverTech Pro"
  },
  "status": "active",
  "status_label": "نشط",
  "created_at": "2023-06-15T14:30:00.000000Z",
  "updated_at": "2023-06-15T15:30:00.000000Z"
}
```

#### استجابة الخطأ (404 Not Found)

```json
{
  "message": "المنتج غير موجود."
}
```

### حذف منتج

يقوم بحذف منتج من النظام.

- **URL**: `/api/products/{id}`
- **Method**: `DELETE`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `id`: معرف المنتج (مطلوب، عدد صحيح)

#### استجابة ناجحة (200 OK)

```json
{
  "message": "تم حذف المنتج بنجاح"
}
```

#### استجابة الخطأ (404 Not Found)

```json
{
  "message": "المنتج غير موجود."
}
```

### تبديل حالة المنتج

يقوم بتبديل حالة المنتج بين نشط وغير نشط.

- **URL**: `/api/products/toggle-status/{id}`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **الصلاحيات**: مشرف فقط
- **معلمات المسار**:
  - `id`: معرف المنتج (مطلوب، عدد صحيح)

#### استجابة ناجحة (200 OK)

```json
{
  "id": 3,
  "name": "انفيرتر 2000 واط",
  "description": "انفيرتر نقي الموجة بقدرة 2000 واط",
  "price": 2500.00,
  "quantity": 15,
  "type": "inverter",
  "type_label": "انفيرتر",
  "specifications": {
    "power": 2000,
    "input_voltage": 24,
    "output_voltage": 220,
    "efficiency": 95.5,
    "dimensions": "40x30x15 cm",
    "weight": 12.5,
    "type": "Pure Sine Wave",
    "brand": "InverTech"
  },
  "status": "inactive",
  "status_label": "غير نشط",
  "created_at": "2023-06-15T14:30:00.000000Z",
  "updated_at": "2023-06-15T16:30:00.000000Z"
}
```

#### استجابة الخطأ (404 Not Found)

```json
{
  "message": "المنتج غير موجود."
}
```

## أنواع المنتجات

### البطاريات

البطاريات هي منتجات تستخدم لتخزين الطاقة الكهربائية. يتم تحديدها بنوع `battery` وتتطلب المواصفات التالية:

- `capacity`: سعة البطارية بالأمبير ساعة (Ah)
- `voltage`: الجهد بالفولت (V)
- `chemistry`: نوع الكيمياء (مثل Lithium-Ion، Lead-Acid، LiFePO4)
- `cycle_life`: عدد دورات الشحن والتفريغ (اختياري)
- `dimensions`: أبعاد البطارية (اختياري)
- `weight`: وزن البطارية بالكيلوغرام (اختياري)
- `brand`: العلامة التجارية (اختياري)

### ألواح الطاقة الشمسية

ألواح الطاقة الشمسية هي منتجات تستخدم لتوليد الكهرباء من أشعة الشمس. يتم تحديدها بنوع `solar_panel` وتتطلب المواصفات التالية:

- `power`: القدرة بالواط (W)
- `voltage`: الجهد بالفولت (V)
- `current`: التيار بالأمبير (A)
- `dimensions`: أبعاد اللوح (اختياري)
- `weight`: وزن اللوح بالكيلوغرام (اختياري)
- `cell_type`: نوع الخلية (مثل Monocrystalline، Polycrystalline، Thin-Film) (اختياري)
- `efficiency`: كفاءة اللوح بالنسبة المئوية (اختياري)
- `brand`: العلامة التجارية (اختياري)

### الإنفيرتر

الإنفيرتر هي منتجات تستخدم لتحويل التيار المستمر (DC) إلى تيار متردد (AC). يتم تحديدها بنوع `inverter` وتتطلب المواصفات التالية:

- `power`: القدرة بالواط (W)
- `input_voltage`: جهد الدخل بالفولت (V)
- `output_voltage`: جهد الخرج بالفولت (V)
- `efficiency`: كفاءة الإنفيرتر بالنسبة المئوية (اختياري)
- `dimensions`: أبعاد الإنفيرتر (اختياري)
- `weight`: وزن الإنفيرتر بالكيلوغرام (اختياري)
- `type`: نوع الإنفيرتر (مثل Pure Sine Wave، Modified Sine Wave، Grid-Tie) (اختياري)
- `brand`: العلامة التجارية (اختياري)

## هيكل البيانات

### نموذج المنتج

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'type', // 'battery', 'solar_panel', 'inverter'
        'specifications', // JSON field for specific attributes
        'status', // 'active', 'inactive'
    ];

    protected $casts = [
        'specifications' => 'array',
        'price' => 'float',
    ];

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

    /**
     * Scope to filter by product type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get batteries
     */
    public function scopeBatteries($query)
    {
        return $query->where('type', 'battery');
    }

    /**
     * Get solar panels
     */
    public function scopeSolarPanels($query)
    {
        return $query->where('type', 'solar_panel');
    }

    /**
     * Get inverters
     */
    public function scopeInverters($query)
    {
        return $query->where('type', 'inverter');
    }
}
```

## ملاحظات هامة

1. يمكن للمستخدمين العاديين عرض المنتجات فقط، بينما يمكن للمشرفين إدارة المنتجات بالكامل (إنشاء، تعديل، حذف).
2. يتم تخزين مواصفات المنتجات في حقل JSON مما يسمح بمرونة في تخزين مواصفات مختلفة لكل نوع من المنتجات.
3. عند حذف منتج، يتم حذف جميع الصور المرتبطة به تلقائيًا.
4. يمكن تصفية المنتجات حسب النوع والحالة، وترتيبها حسب أي حقل.
5. يمكن تضمين الصور الرئيسية أو جميع الصور في استجابة API حسب الحاجة.
6. يتم التحقق من صحة المواصفات المطلوبة لكل نوع من المنتجات عند الإنشاء أو التحديث. 