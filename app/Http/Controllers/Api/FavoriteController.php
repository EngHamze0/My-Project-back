<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * عرض قائمة المنتجات المفضلة للمستخدم الحالي.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $favorites = $user->favoriteProducts()->with('primaryImage')->get();

        return response()->json([
            'status' => 'success',
            'data' => $favorites
        ]);
    }

    /**
     * إضافة منتج إلى المفضلة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();
        $productId = $request->product_id;

        // التحقق مما إذا كان المنتج موجودًا بالفعل في المفضلة
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'المنتج موجود بالفعل في المفضلة'
            ], 422);
        }

        // إضافة المنتج إلى المفضلة
        $favorite = Favorite::create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        // الحصول على معلومات المنتج
        $product = Product::with('primaryImage')->find($productId);

        return response()->json([
            'status' => 'success',
            'message' => 'تمت إضافة المنتج إلى المفضلة بنجاح',
            'data' => $product
        ], 201);
    }

    /**
     * حذف منتج من المفضلة.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($productId)
    {
        $user = Auth::user();
        
        // البحث عن المنتج في المفضلة
        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'المنتج غير موجود في المفضلة'
            ], 404);
        }

        // حذف المنتج من المفضلة
        $favorite->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف المنتج من المفضلة بنجاح'
        ]);
    }

    /**
     * التبديل بين إضافة/حذف المنتج من المفضلة.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle($productId)
    {
        $user = Auth::user();
        
        // التحقق من وجود المنتج
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'المنتج غير موجود'
            ], 404);
        }

        // البحث عن المنتج في المفضلة
        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($favorite) {
            // إذا كان المنتج موجودًا في المفضلة، قم بحذفه
            $favorite->delete();
            $message = 'تم حذف المنتج من المفضلة بنجاح';
            $isFavorite = false;
        } else {
            // إذا لم يكن المنتج موجودًا في المفضلة، قم بإضافته
            Favorite::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            $message = 'تمت إضافة المنتج إلى المفضلة بنجاح';
            $isFavorite = true;
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'is_favorite' => $isFavorite
        ]);
    }

    /**
     * التحقق مما إذا كان المنتج في المفضلة.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function check($productId)
    {
        $user = Auth::user();
        
        // التحقق من وجود المنتج في المفضلة
        $isFavorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'status' => 'success',
            'is_favorite' => $isFavorite
        ]);
    }
} 