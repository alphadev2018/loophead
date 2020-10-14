<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>

<div style="max-width: 420px; margin: auto; width: 100%; text-align: center;">

    <h1>Purchase Receipt</h1>
    <label>from Loophead.net</label>

    <table style="margin: 20px 0; padding: 5px 10px; border: 1px solid #e9ebf1;" width="100%">
        <tr>
            <td align="left" style="padding: 10px 0;">Invoice Number</td>
            <td align="right" style="padding: 10px 0;">{{$order->transaction_id}}</td>
        </tr>
        <tr>
            <td align="left" style="padding: 10px 0;">Invoice Date</td>
            <td align="right" style="padding: 10px 0;">{{$order->updated_at}}</td>
        </tr>
        <tr>
            <td align="left" style="padding: 10px 0;">Bill to</td>
            <td align="right" style="padding: 10px 0;">
                <label style="text-align: right; color: red;">
                    {{$user_name}}<br/>
                    <a href="#" style="color: red;">{{$user_email}}</a>
                </label>
            </td>
        </tr>
        <tr>
            <td align="left" style="padding: 10px 0;">Subtotal</td>
            <td align="right" style="padding: 10px 0;">${{$order->amount}}</td>
        </tr>
        <tr>
            <td align="left" style="padding: 10px 0;">Discounts</td>
            <td align="right" style="padding: 10px 0;">-$0</td>
        </tr>
        <tr>
            <td align="left" style="padding: 10px 0;">Total</td>
            <td align="right" style="padding: 10px 0;">${{$order->amount}}</td>
        </tr>
        <tr>
            <td align="left" style="padding: 10px 0;">
                <label style="font-weight: 600;">{{$order->product->name}}</label>
                <p style="margin: 0; color: grey;">{{$order->product_type === 'App\Loop' ? 'Loop':'Soundkit'}}</p>
            </td>
            <td align="right" style="padding: 10px 0;">${{$order->amount}}</td>
        </tr>
    </table>

    <a href="{{$download_url}}" style="display: block; text-decoration: none; padding: 10px 30px; background: #fe2b0d; border-radius: 5px; color: white; margin: auto;">Download Files</a>
</div>

</body>
</html>