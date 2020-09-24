<?php namespace Common\Billing\Gateways\Paypal;

use Common\Billing\BillingPlan;
use Common\Billing\GatewayException;
use Common\Billing\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Omnipay\Omnipay;

class PaypalController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var BillingPlan
     */
    private $billingPlan;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var PaypalGateway
     */
    private $paypal;

    /**
     * @param Request $request
     * @param BillingPlan $billingPlan
     * @param Subscription $subscription
     * @param PaypalGateway $paypal
     */
    public function __construct(
        Request $request,
        BillingPlan $billingPlan,
        Subscription $subscription,
        PaypalGateway $paypal
    )
    {
        $this->paypal = $paypal;
        $this->request = $request;
        $this->billingPlan = $billingPlan;
        $this->subscription = $subscription;

        $this->middleware('auth', ['except' => [
            'approvedCallback', 'canceledCallback', 'loadingPopup', 'buyerTest']
        ]);
    }
    
    /**
     * Create subscription agreement on paypal.
     *
     * @return JsonResponse
     * @throws GatewayException
     */
    public function createSubscriptionAgreement()
    {
        $this->validate($this->request, [
            'plan_id' => 'required|integer|exists:billing_plans,id',
            'start_date' => 'string'
        ]);

        $urls = $this->paypal->subscriptions()->create(
            $this->billingPlan->findOrFail($this->request->get('plan_id')),
            $this->request->user(),
            $this->request->get('start_date')
        );

        return $this->success(['urls' => $urls]);
    }

    /**
     * Execute subscription agreement on paypal.
     *
     * @return JsonResponse
     * @throws GatewayException
     */
    public function executeSubscriptionAgreement()
    {
        $this->validate($this->request, [
            'agreement_id' => 'required|string|min:1',
            'plan_id' => 'required|integer|exists:billing_plans,id',
        ]);

        $subscriptionId = $this->paypal->subscriptions()->executeAgreement(
            $this->request->get('agreement_id')
        );

        $plan = $this->billingPlan->findOrFail($this->request->get('plan_id'));
        $this->request->user()->subscribe('paypal', $subscriptionId, $plan);

        return $this->success(['user' => $this->request->user()->loadPermissions()->load('subscriptions.plan')]);
    }

    /**
     * Called after user approves paypal payment.
     */
    public function approvedCallback()
    {
        return view('common::billing/paypal-popup')->with([
            'token' => $this->request->get('token'),
            'status' => 'success',
        ]);
    }

    /**
     * Called after user cancels paypal payment.
     */
    public function canceledCallback()
    {
        return view('common::billing/paypal-popup')->with([
            'token' => $this->request->get('token'),
            'status' => 'cancelled',
        ]);
    }

    /**
     * Show loading view for paypal.
     */
    public function loadingPopup()
    {
        return view('common::billing/loading-popup');
    }


    public function buyerTest()
    {
        $gateway = Omnipay::create('PayPal_Rest');

        $gateway->initialize([
            'clientId' => config('services.paypal.client_id'),
            'secret' => config('services.paypal.secret'),
            'testMode' => true,
        ]);

        $card = new \Omnipay\Common\CreditCard(array(
            'firstName' => 'Lee',
            'lastName' => 'Xian',
            'number' => '4032031706650321',
            'expiryMonth'           => '08',
            'expiryYear'            => '2025',
            'cvv'                   => '123',
            'billingAddress1'       => '1 Scrubby Creek Road',
            'billingCountry'        => 'AU',
            'billingCity'           => 'Scrubby Creek',
            'billingPostcode'       => '4999',
            'billingState'          => 'QLD',
        ));
        
        // Do an authorisation transaction on the gateway
        $transaction = $gateway->authorize(array(
            'amount'        => '10.00',
            'currency'      => 'USD',
            'description'   => 'This is a test authorize transaction.',
            'card'          => $card,
        ));
        $response = $transaction->send();
        if ($response->isSuccessful()) {
            echo "Authorize transaction was successful!\n";
            // Find the authorization ID
            $auth_id = $response->getTransactionReference();

            echo $auth_id;
        }
    }
}
