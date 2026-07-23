<?php

namespace App\Repositories\Api\Payments;

use App\Models\PaymentLink;
use Illuminate\Support\Facades\DB;

class StripeRepository
{
    private $data = [];

    public function check($request)
    {
        $response = [
            "status" => false,
        ];

        $this->data = $request->all();

        $rez = DB::select("
            SELECT
                rez.id,
                rez.currency,
                site.payment_domain,
                ROUND(COALESCE(SUM(s.total_sales), 0), 2) AS total_sales,
                ROUND(COALESCE(SUM(p.total_payments), 0), 2) AS total_payments
            FROM reservations AS rez

            LEFT JOIN (
                SELECT
                    reservation_id,
                    ROUND(COALESCE(SUM(total), 0), 2) AS total_sales
                FROM sales
                WHERE deleted_at IS NULL
                  AND sales.sale_type_id <> 3
                GROUP BY reservation_id
            ) AS s
                ON s.reservation_id = rez.id

            LEFT JOIN (
                SELECT
                    reservation_id,
                    ROUND(
                        SUM(
                            CASE
                                WHEN operation = 'multiplication'
                                    THEN total * exchange_rate
                                WHEN operation = 'division'
                                    THEN total / exchange_rate
                                ELSE total
                            END
                        ),
                        2
                    ) AS total_payments,
                    GROUP_CONCAT(
                        DISTINCT payment_method
                        ORDER BY payment_method ASC
                        SEPARATOR ','
                    ) AS payment_type_name
                FROM payments
                GROUP BY reservation_id
            ) AS p
                ON p.reservation_id = rez.id

            INNER JOIN sites AS site
                ON site.id = rez.site_id

            WHERE rez.id = :code
              AND rez.is_cancelled = 0

            GROUP BY
                rez.id,
                rez.currency,
                site.payment_domain
        ", [
            'code' => $this->data['id'],
        ]);

        if (sizeof($rez) <= 0) {
            $response['code'] = "cancelled";
            $response['message'] = "Your reservation has been cancelled, if you want to reactivate it contact us.";

            return $response;
        }

        $total = (float) $rez[0]->total_sales
            - (float) $rez[0]->total_payments;

        if ($total <= 0) {
            $response['code'] = "payments";
            $response['message'] = "No payments to be made";

            return $response;
        }

        /*
        |--------------------------------------------------------------------------
        | Sin conversión de moneda
        |--------------------------------------------------------------------------
        |
        | Se usa directamente el total pendiente y la moneda de la reservación.
        | No se convierte a MXN ni se consulta payments_exchange_rate.
        |
        */

        $currency = strtoupper($rez[0]->currency);
        $total = round($total, 2);

        /*
        |--------------------------------------------------------------------------
        | Enlace de pago
        |--------------------------------------------------------------------------
        |
        | Si existe un PaymentLink válido, se usa directamente su monto
        | y su moneda, sin ningún tipo de cambio.
        |
        */

        if ($request->link_code) {
            $paymentLink = PaymentLink::where(
                'link_code',
                $request->link_code
            )->first();

            if (
                $paymentLink &&
                $paymentLink->currency &&
                $paymentLink->amount
            ) {
                $total = round((float) $paymentLink->amount, 2);
                $currency = strtoupper($paymentLink->currency);
            }
        }

        $data = [
            "total" => $total,
            "currency" => $currency,
            "payment_domain" => $rez[0]->payment_domain,
        ];

        try {
            $key = config('services.stripe.key');

            $stripe = new \Stripe\StripeClient($key);

            $product = $stripe->products->create([
                'name' => $request->language === "en"
                    ? 'Transportation service'
                    : 'Servicio de transportación',
            ]);

            $price = $stripe->prices->create([
                'unit_amount' => (int) round($data['total'] * 100),
                'currency' => strtolower($data['currency']),
                'product' => $product->id,
            ]);

            $checkoutSession = $stripe->checkout->sessions->create([
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $data['payment_domain'] . $request->success_url,
                'cancel_url' => $data['payment_domain'] . $request->cancel_url,
                'payment_intent_data' => [
                    'metadata' => [
                        'reservation_id' => $request->id,
                    ],
                ],
            ]);

            $response['status'] = true;
            $response['data'] = [
                'url' => $checkoutSession->url,
            ];

            return $response;
        } catch (\Exception $e) {
            $response['code'] = "stripe";
            $response['message'] = $e->getMessage();

            return $response;
        }
    }

    public function createIntent($amount, $currency, $reservationId)
    {
        $key = config('services.stripe.key');

        $stripe = new \Stripe\StripeClient($key);

        return $stripe->paymentIntents->create([
            'amount' => (int) round(((float) $amount) * 100),
            'currency' => strtolower($currency),
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => [
                'reservation_id' => $reservationId,
            ],
        ]);
    }

    public function getIntent($intentId)
    {
        $key = config('services.stripe.key');

        $stripe = new \Stripe\StripeClient($key);

        return $stripe->paymentIntents->retrieve(
            $intentId,
            []
        );
    }
}
