<?php

namespace App\Http\Controllers;

use Auth;
use Str;
use App\Order;
use App\PayPal;
use App\Loop;
use App\Soundkit;

use Common\Billing\Gateways\Paypal\PaypalController;
use Illuminate\Http\Request;

/**
 * Class PaymentController
 * @package App\Http\Controllers
 */
class PaymentController extends Controller
{
    /**
     * @param $order_id
     * @param Request $request
     */
    public function checkout($product_id, $product_type, Request $request)
    {
        $product = null;
        if ($product_type === 'loop') {
            $product = Loop::findOrFail($product_id);
        } else {
            $product = Soundkit::findOrFail($product_id);
        }

        $order = new Order;
        $order->transaction_id = Str::uuid();
        $order->amount = $product->cost;
        $order->product_id = $product->id;
        $order->product_type = $product->model_type;
        $order->user_id = Auth::user()->id;
        $order->save();

        $paypal = new PayPal;

        $response = $paypal->purchase(array(
            'amount' => $paypal->formatAmount($order->amount),
            'transactionId' => $order->id,
            'currency' => 'USD',
            'cancelUrl' => $paypal->getCancelUrl($order),
            'returnUrl' => $paypal->getReturnUrl($order),
        ));

        if ($response->isRedirect()) {
            $response->redirect();
        }

        return redirect()->back()->with([
            'message' => $response->getMessage(),
        ]);
    }

    /**
     * @param $order_id
     * @param Request $request
     * @return mixed
     */
    public function completed($order_id, Request $request)
    {
        $order = Order::findOrFail($order_id);

        $payerId = $request->get('PayerID');
        $paymentId = $request->get('paymentId');

        $paypal = new PayPal;

        $response = $paypal->complete([
            'amount' => $paypal->formatAmount($order->amount),
            'transactionId' => $order->transaction_id,
            'payerId' => $payerId,
            'transactionReference' => $paymentId,
            'currency' => 'USD',
            'cancelUrl' => $paypal->getCancelUrl($order),
            'returnUrl' => $paypal->getReturnUrl($order),
            'notifyUrl' => $paypal->getNotifyUrl($order),
        ]);

        if ($response->isSuccessful()) {
            $order->update([
                // 'transaction_id' => $response->getTransactionReference(),
                'status' => Order::PAYMENT_COMPLETED,
            ]);

            // return redirect()->route('order.paypal', encrypt($order_id))->with([
            //     'message' => 'You recent payment is sucessful with reference code ' . $response->getTransactionReference(),
            // ]);
            return redirect()->to('/download/'.($order->product_type == 'App\Loop' ? 'loop':'soundkit').'/'.$order->product_id);
        }

        return redirect()->back()->with([
            'message' => $response->getMessage(),
        ]);
    }

    /**
     * @param $order_id
     */
    public function cancelled($order_id)
    {
        $order = Order::findOrFail($order_id);

        return redirect()->route('order.paypal', encrypt($order_id))->with([
            'message' => 'You have cancelled your recent PayPal payment !',
        ]);
    }

        /**
     * @param $order_id
     * @param $env
     * @param Request $request
     */
    public function webhook($order_id, $env, Request $request)
    {
        // to do with new release of sudiptpa/paypal-ipn v3.0 (under development)
    }
}