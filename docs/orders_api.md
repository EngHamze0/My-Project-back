# وثائق واجهة برمجة التطبيقات (API) لنظام الطلبات

## نظرة عامة

يوفر نظام الطلبات واجهة برمجة تطبيقات RESTful تسمح للمستخدمين بإنشاء الطلبات وإدارتها ومتابعة حالتها. كما يسمح للمشرفين بعرض جميع الطلبات وتحديث حالتها.

## المصادقة

جميع طلبات API تتطلب مصادقة باستخدام Laravel Sanctum. يجب تضمين رمز الوصول (Access Token) في رأس الطلب:

```
Authorization: Bearer {your-token}
```

## نقاط النهاية (Endpoints)

### 1. إنشاء طلب جديد

ينشئ طلبًا جديدًا باستخدام مصفوفة من معرفات المنتجات.

- **URL**: `/api/orders`
- **Method**: `POST`
- **المصادقة**: مطلوبة
- **المعلمات**:

  | المعلمة | النوع | الوصف | مطلوب |
  |---------|------|-------------|----------|
  | products | مصفوفة | مصفوفة من المنتجات للطلب | نعم |
  | products[].product_id | رقم | معرف المنتج | نعم |
  | products[].quantity | رقم | كمية المنتج (الافتراضي: 1) | لا |
  | coupon_code | نص | رمز الكوبون للخصم | لا |

- **مثال للطلب**:
  ```json
  {
    "products": [
      {
        "product_id": 1,
        "quantity": 2
      },
      {
        "product_id": 3,
        "quantity": 1
      }
    ],
    "coupon_code": "SUMMER2023"
  }
  ```

- **الاستجابة الناجحة**: `201 Created`
  ```json
  {
    "status": "success",
    "message": "تم إنشاء الطلب بنجاح",
    "data": {
      "id": 1,
      "order_number": "ORD-20230615123456-ABC12",
      "status": "pending",
      "subtotal": 150.00,
      "discount": 15.00,
      "total": 135.00,
      "coupon_code": "SUMMER2023",
      "payment_method": "cash",
      "payment_status": "pending",
      "notes": null,
      "items": [
        {
          "id": 1,
          "order_id": 1,
          "product_id": 1,
          "quantity": 2,
          "price": 50.00,
          "total_price": 100.00,
          "product_details": {
            "id": 1,
            "name": "اسم المنتج الأول",
            "price": 50.00,
            "image": "url-to-image"
          }
        },
        {
          "id": 2,
          "order_id": 1,
          "product_id": 3,
          "quantity": 1,
          "price": 50.00,
          "total_price": 50.00,
          "product_details": {
            "id": 3,
            "name": "اسم المنتج الثالث",
            "price": 50.00,
            "image": "url-to-image"
          }
        }
      ],
      "created_at": "2023-06-15T12:34:56.000000Z",
      "updated_at": "2023-06-15T12:34:56.000000Z"
    }
  }
  ```

- **الاستجابات الخاطئة**:
  - `400 Bad Request`: معلمات غير صحيحة
  - `404 Not Found`: منتج غير موجود
  - `500 Internal Server Error`: خطأ في الخادم

### 2. الحصول على طلبات المستخدم

يعرض قائمة بجميع طلبات المستخدم الحالي.

- **URL**: `/api/orders/user`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **المعلمات**:

  | المعلمة | النوع | الوصف | مطلوب |
  |---------|------|-------------|----------|
  | page | رقم | رقم الصفحة (الافتراضي: 1) | لا |

- **الاستجابة الناجحة**: `200 OK`
  ```json
  {
    "status": "success",
    "message": "تم الحصول على قائمة الطلبات بنجاح",
    "data": {
      "data": [
        {
          "id": 1,
          "order_number": "ORD-20230615123456-ABC12",
          "status": "pending",
          "subtotal": 150.00,
          "discount": 15.00,
          "total": 135.00,
          "coupon_code": "SUMMER2023",
          "payment_method": "cash",
          "payment_status": "pending",
          "notes": null,
          "items": [
            {
              "id": 1,
              "order_id": 1,
              "product_id": 1,
              "quantity": 2,
              "price": 50.00,
              "total_price": 100.00,
              "product_details": {
                "id": 1,
                "name": "اسم المنتج الأول",
                "price": 50.00,
                "image": "url-to-image"
              }
            }
          ],
          "created_at": "2023-06-15T12:34:56.000000Z",
          "updated_at": "2023-06-15T12:34:56.000000Z"
        }
      ],
      "pagination": {
        "total": 5,
        "count": 1,
        "per_page": 10,
        "current_page": 1,
        "total_pages": 1,
        "has_more_pages": false
      }
    }
  }
  ```

### 3. الحصول على تفاصيل طلب محدد للمستخدم

يعرض تفاصيل طلب محدد للمستخدم الحالي.

- **URL**: `/api/orders/user/{orderId}`
- **Method**: `GET`
- **المصادقة**: مطلوبة
- **معلمات المسار**:

  | المعلمة | الوصف | مطلوب |
  |---------|-------------|----------|
  | orderId | معرف الطلب | نعم |

- **الاستجابة الناجحة**: `200 OK`
  ```json
  {
    "status": "success",
    "message": "تم الحصول على تفاصيل الطلب بنجاح",
    "data": {
      "id": 1,
      "order_number": "ORD-20230615123456-ABC12",
      "status": "pending",
      "subtotal": 150.00,
      "discount": 15.00,
      "total": 135.00,
      "coupon_code": "SUMMER2023",
      "payment_method": "cash",
      "payment_status": "pending",
      "notes": null,
      "items": [
        {
          "id": 1,
          "order_id": 1,
          "product_id": 1,
          "quantity": 2,
          "price": 50.00,
          "total_price": 100.00,
          "product_details": {
            "id": 1,
            "name": "اسم المنتج الأول",
            "price": 50.00,
            "image": "url-to-image"
          }
        },
        {
          "id": 2,
          "order_id": 1,
          "product_id": 3,
          "quantity": 1,
          "price": 50.00,
          "total_price": 50.00,
          "product_details": {
            "id": 3,
            "name": "اسم المنتج الثالث",
            "price": 50.00,
            "image": "url-to-image"
          }
        }
      ],
      "created_at": "2023-06-15T12:34:56.000000Z",
      "updated_at": "2023-06-15T12:34:56.000000Z"
    }
  }
  ```

- **الاستجابات الخاطئة**:
  - `404 Not Found`: الطلب غير موجود أو لا ينتمي للمستخدم الحالي

### 4. الحصول على جميع الطلبات (للمشرف)

يعرض قائمة بجميع الطلبات في النظام (للمشرفين فقط).

- **URL**: `/api/admin/orders`
- **Method**: `GET`
- **المصادقة**: مطلوبة (المشرف فقط)
- **المعلمات**:

  | المعلمة | النوع | الوصف | مطلوب |
  |---------|------|-------------|----------|
  | page | رقم | رقم الصفحة (الافتراضي: 1) | لا |

- **الاستجابة الناجحة**: `200 OK`
  ```json
  {
    "status": "success",
    "message": "تم الحصول على قائمة الطلبات بنجاح",
    "data": {
      "data": [
        {
          "id": 1,
          "order_number": "ORD-20230615123456-ABC12",
          "status": "pending",
          "subtotal": 150.00,
          "discount": 15.00,
          "total": 135.00,
          "coupon_code": "SUMMER2023",
          "payment_method": "cash",
          "payment_status": "pending",
          "notes": null,
          "user": {
            "id": 5,
            "name": "اسم المستخدم",
            "email": "user@example.com"
          },
          "created_at": "2023-06-15T12:34:56.000000Z",
          "updated_at": "2023-06-15T12:34:56.000000Z"
        }
      ],
      "pagination": {
        "total": 15,
        "count": 10,
        "per_page": 10,
        "current_page": 1,
        "total_pages": 2,
        "has_more_pages": true
      }
    }
  }
  ```

- **الاستجابات الخاطئة**:
  - `403 Forbidden`: المستخدم ليس مشرفًا

### 5. الحصول على تفاصيل طلب محدد (للمشرف)

يعرض تفاصيل طلب محدد للمشرف.

- **URL**: `/api/admin/orders/{orderId}`
- **Method**: `GET`
- **المصادقة**: مطلوبة (المشرف فقط)
- **معلمات المسار**:

  | المعلمة | الوصف | مطلوب |
  |---------|-------------|----------|
  | orderId | معرف الطلب | نعم |

- **الاستجابة الناجحة**: `200 OK`
  ```json
  {
    "status": "success",
    "message": "تم الحصول على تفاصيل الطلب بنجاح",
    "data": {
      "id": 1,
      "order_number": "ORD-20230615123456-ABC12",
      "status": "pending",
      "subtotal": 150.00,
      "discount": 15.00,
      "total": 135.00,
      "coupon_code": "SUMMER2023",
      "payment_method": "cash",
      "payment_status": "pending",
      "notes": null,
      "items": [
        {
          "id": 1,
          "order_id": 1,
          "product_id": 1,
          "quantity": 2,
          "price": 50.00,
          "total_price": 100.00,
          "product_details": {
            "id": 1,
            "name": "اسم المنتج الأول",
            "price": 50.00,
            "image": "url-to-image"
          }
        }
      ],
      "user": {
        "id": 5,
        "name": "اسم المستخدم",
        "email": "user@example.com"
      },
      "created_at": "2023-06-15T12:34:56.000000Z",
      "updated_at": "2023-06-15T12:34:56.000000Z"
    }
  }
  ```

- **الاستجابات الخاطئة**:
  - `403 Forbidden`: المستخدم ليس مشرفًا
  - `404 Not Found`: الطلب غير موجود

### 6. تحديث حالة الطلب (للمشرف)

يقوم بتحديث حالة طلب محدد (للمشرفين فقط).

- **URL**: `/api/admin/orders/{orderId}/status`
- **Method**: `POST`
- **المصادقة**: مطلوبة (المشرف فقط)
- **معلمات المسار**:

  | المعلمة | الوصف | مطلوب |
  |---------|-------------|----------|
  | orderId | معرف الطلب | نعم |

- **المعلمات**:

  | المعلمة | النوع | الوصف | مطلوب |
  |---------|------|-------------|----------|
  | status | نص | حالة الطلب الجديدة (pending, processing, completed, cancelled, refunded) | نعم |

- **مثال للطلب**:
  ```json
  {
    "status": "completed"
  }
  ```

- **الاستجابة الناجحة**: `200 OK`
  ```json
  {
    "status": "success",
    "message": "تم تحديث حالة الطلب بنجاح",
    "data": {
      "id": 1,
      "order_number": "ORD-20230615123456-ABC12",
      "status": "completed",
      "subtotal": 150.00,
      "discount": 15.00,
      "total": 135.00,
      "coupon_code": "SUMMER2023",
      "payment_method": "cash",
      "payment_status": "pending",
      "notes": null,
      "items": [...],
      "created_at": "2023-06-15T12:34:56.000000Z",
      "updated_at": "2023-06-15T13:45:00.000000Z"
    }
  }
  ```

- **الاستجابات الخاطئة**:
  - `400 Bad Request`: حالة غير صالحة
  - `403 Forbidden`: المستخدم ليس مشرفًا
  - `404 Not Found`: الطلب غير موجود

## أكواد الحالة

- `200 OK`: تم تنفيذ الطلب بنجاح
- `201 Created`: تم إنشاء المورد بنجاح
- `400 Bad Request`: معلمات غير صحيحة
- `401 Unauthorized`: المصادقة مطلوبة
- `403 Forbidden`: ليس لديك الصلاحيات الكافية
- `404 Not Found`: المورد غير موجود
- `500 Internal Server Error`: خطأ في الخادم

## حالات الطلب

- `pending`: في انتظار المراجعة
- `processing`: قيد المعالجة
- `completed`: مكتمل
- `cancelled`: ملغي
- `refunded`: مسترد 