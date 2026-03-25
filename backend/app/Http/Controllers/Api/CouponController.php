<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CouponCode;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        // 1️⃣ Basic validation
        $validator = Validator::make($request->all(), [
            'couponCode' => [
                'required',
                'regex:/^[A-Z0-9_-]{4,20}$/'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error_code' => 'INVALID_FORMAT',
                'message' => 'कूपन कोड स्वरूप चुकीचे आहे'
            ], 422);
        }

        $code = strtoupper(trim($request->couponCode));

        // 2️⃣ Fetch coupon
        $coupon = CouponCode::where('coupon', $code)->first();

        if (!$coupon) {
            return response()->json([
                'status' => false,
                'error_code' => 'NOT_FOUND',
                'message' => 'कूपन कोड अस्तित्वात नाही'
            ], 404);
        }

        // 3️⃣ Business rules
        if ($coupon->isDeleted) {
            return response()->json([
                'status' => false,
                'error_code' => 'DELETED',
                'message' => 'हा कूपन कोड वैध नाही'
            ], 400);
        }

        if ($coupon->isActivated || $coupon->userId > 0) {
            return response()->json([
                'status' => false,
                'error_code' => 'ALREADY_USED',
                'message' => 'हा कूपन कोड आधीच वापरलेला आहे'
            ], 409);
        }

        // 4️⃣ Valid coupon
        return response()->json([
            'status' => true,
            'message' => 'कूपन कोड वैध आहे'
        ]);
    }
}
