<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfferingV3;
use App\Models\Product;
use App\Services\BookingContextBuilder;
use Illuminate\Http\Request;

class BookingLinkController extends Controller
{
    public function __construct(
        private BookingContextBuilder $contextBuilder
    ) {}

    public function availability(Request $request, OfferingV3 $offering)
    {
        $context = $this->contextBuilder->buildForOffering(
            $offering,
            $this->resolvePriceOptionId($request),
            $this->resolveVariantLabel($request)
        );

        return response()->json([
            'bookingPayload' => $context['bookingPayload'],
        ]);
    }

    public function availabilityForProduct(Request $request, Product $product)
    {
        $context = $this->contextBuilder->buildForProduct(
            $product,
            $this->resolvePriceOptionId($request),
            $this->resolveVariantLabel($request)
        );

        return response()->json([
            'bookingPayload' => $context['bookingPayload'],
        ]);
    }

    private function resolvePriceOptionId(Request $request): ?int
    {
        $value = $request->input('price_option_id');
        if ($value === null || $value === '') {
            $value = $request->query('price_option_id');
        }

        if ($value === null || $value === '') {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);
        return $integer === false ? null : (int) $integer;
    }

    private function resolveVariantLabel(Request $request): ?string
    {
        $value = $request->input('variant_label');
        if ($value === null || trim((string) $value) === '') {
            $value = $request->query('variant_label');
        }

        if ($value === null || trim((string) $value) === '') {
            $value = $request->input('session_label');
        }
        if ($value === null || trim((string) $value) === '') {
            $value = $request->query('session_label');
        }

        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
