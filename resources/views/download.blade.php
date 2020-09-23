<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700;800&display=swap" rel="stylesheet">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="/assets/css/bootstrap.min.css" />
        <link rel="stylesheet" href="/assets/css/style.css" />

        <title>Download</title>
    </head>
    <body>
        <header class="sticky-top">
            <nav class="navbar navbar-expand-md main_header">
                <div class="container-fluid">
                    <a class="navbar-brand" style="width: 75px;" href="/"><img src="/assets/img/logo.png" alt="" /></a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav_one" aria-controls="nav_one" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="collapse navbar-collapse" id="nav_one">
                        <ul class="navbar-nav mr-auto first">
                            <li class="nav-item">
                              <a class="nav-link" aria-current="page" href="/ranking">Ranking</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="/" id="navbarDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
                                    Browse
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                  <li><a class="dropdown-item" href="/browse/top-downloaded">Top Downloaded Media</a></li>
                                  <li><a class="dropdown-item" href="/browse/top-makers">Top Loop Makers</a></li>
                                  <li><a class="dropdown-item" href="/browse/featured-makers">Featured Loop Makers</a></li>
                                  <li><a class="dropdown-item" href="/browse/staff-picked">Staff Picked Loops</a></li>
                                </ul>
                              </li>
                            <li class="nav-item">
                              <a class="nav-link" href="/myfeed">My Feed </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <!-- End header -->
        <div class="banner">
            <h1>Download {{ $media->model_type === 'App\Soundkit' ? 'Soundkit':'Loop' }}</h1>
        </div>
        
        <div class="container my-4">

            <div class="title-area mt-3 text-center">
                <h2 class="title">Please checkout before download!</h2>
                <p>This media is not free, you need to checkout payment before download</p>
            </div>
            
            <div class="row">
                @if(session('message'))
                    <script>alert("{{session('message')}}");</script>
                @endif
                <div class="col-md-6 col-lg-6">
                    <div class="payment-option-container">
                        <div class="payment-heading">
                            <h3>{{ $media->model_type === 'App\Soundkit' ? 'Soundkit':'Loop' }} Details</h3>
                        </div>
                        <div class="saved-card-container">
                            <img src="{{ $media->image ? '/'.$media->image :  '/client/assets/images/default/album.png'}}" />
                            <h4>Name</h4>
                            <div class="ml-3">
                                @if ($media->model_type === 'App\Soundkit')
                                <a href="/soundkit/{{$media->id}}/{{$media->artist->name}}/{{ Str::slug($media->name, '-') }}" style="color: #a0a9bf;font-size: 14px;line-height: 27px;">
                                    {{ $media->name }}
                                </a>
                                @else
                                <a href="/loop/{{$media->id}}/{{ Str::slug($media->name, '-') }}" style="color: #a0a9bf;font-size: 14px;line-height: 27px;">
                                    {{ $media->name }}
                                </a>
                                @endif
                            </div>

                            <h4>Artist</h4>
                            <div class="ml-3 mb-3">
                                @if ($media->model_type === 'App\Soundkit')
                                <a href="/user/{{$media->artist->id}}/{{$media->artist->name}}" style="color: #a0a9bf;font-size: 14px;line-height: 27px;">
                                    {{ $media->artist->name }}
                                </a>
                                @else
                                <a href="/user/{{$media->artists[0]['id']}}/{{$media->artists[0]['name']}}" style="color: #a0a9bf;font-size: 14px;line-height: 27px;">
                                    {{$media->artists[0]['name']}}
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-6 col-lg-4  offset-lg-2">
                    <div class="order-summary">
                        
                        <form method="POST" action="/checkout/payment/{{$media->id}}/{{$media->model_type === 'App\Soundkit' ? 'soundkit':'loop' }}/paypal">
                            {{csrf_field()}}
                            <div class="summary-container">
                                <h4>Order Summary</h4>
                                <!-- <p class="text-left">#00001215</p> -->
                            </div>
                            <div class="summary-container py-2">
                                <div class="items d-flex justify-content-between align-items-center mb-2">
                                    <div class="key">Date</div>
                                    <div class="value">{{ date('d M Y')}}</div>
                                </div>
                                <div class="items d-flex justify-content-between align-items-center">
                                    <div class="key">Time</div>
                                    <div class="value">{{ date('h:i A') }}</div>
                                </div>
                            </div>
                            <div class="summary-container py-2">
                                <h5>Items</h5>
                                <div class="items d-flex justify-content-between align-items-center mb-2">
                                    <div class="key"> {{ $media->name }} </div>
                                    <div class="value"><strong>${{ $media->cost }}</strong></div>
                                </div>
                            </div>
                            <div class="summary-container py-2">
                                <div class="items d-flex justify-content-between align-items-center mb-2">
                                    <div class="key">Subtotal</div>
                                    <div class="value"><strong>${{ $media->cost }}</strong></div>
                                </div>
                                <div class="items d-flex justify-content-between align-items-center mb-2">
                                    <div class="key">Tax</div>
                                    <div class="value"><strong>$0</strong></div>
                                </div>
                                <!-- <div class="items d-flex justify-content-between align-items-center mb-2">
                                    <div class="key">Coupon</div>
                                    <div class="value"><input type="text" class="form-control rounded-pill" placeholder="MAX 5"></div>
                                </div> -->
                            </div>
                            <div class="summary-container py-2 border-0">
                                <div class="items d-flex justify-content-between align-items-center mb-2">
                                    <div class="key">Total</div>
                                    <div class="value"><strong>${{ $media->cost }}</strong></div>
                                </div>
                            </div>
                            <div class="terms">
                                <input type="radio" class="custom-radio success">
                                <div class="conditions">
                                    <p>Before Paying yoummust agree with</p>
                                    <a href="#">Terms & Conditions</a>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <button type="submit" class="btn btn-info btn-block">Proceed to Pay</button>                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <div class="container">
                <div class="row">
                    <div class="col-lg-2 col-md-3 d-flex align-items-center footer_logo">
                         <a href="#"><img src="/assets/img/logo-yellow.png" alt=""></a>
                    </div>
                    <div class="col-lg-8 col-md-9">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="widget">
                                    <h3>MAIN LINKS</h3>
                                    <ul>
                                        <li><a href="index.html">Home</a></li>
                                        <li><a href="ranking.html">Ranking</a></li>
                                        <li><a href="#">Browse</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="widget">
                                    <h3>INFO</h3>
                                    <ul>
                                        <li><a href="about.html">About</a></li>
                                        <li><a href="contact.html">Contact</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="widget">
                                    <h3>SUPPORT</h3>
                                    <ul>
                                        <li><a href="faqs.html">FAQs</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="widget">
                                    <h3>LEGAL</h3>
                                    <ul>
                                        <li><a href="#">Teams & Conditions</a></li>
                                        <li><a href="#">Privacy Policy</a></li>
                                        <li><a href="cookie-policy.html">Cookie Policy</a></li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-2 col-md-12 socila_area">
                        <div class="widget">
                            <h3>Social media</h3>
                            <div class="socila_media">
                                <a href=""><i class="fab fa-twitter"></i></a>
                                <a href=""><i class="fab fa-facebook-f"></i></a>
                                <a href=""><i class="fab fa-pinterest-p"></i></a>
                                <a href=""><i class="fab fa-instagram"></i></a>
                                <a href=""><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bottomFooter">
                <div class="container">
                    <div class="w-100 d-flex align-items-center justify-content-between">
                        <div class="left">
                            &copy;2020 LOOPHEAD.NET ALL RIGHTS RESERVED
                        </div>

                        <ul class="paymentLink align-items-center">
                            <li>
                                WE ACCEPT ALL CARDS
                            </li>
                            <li>
                                <img src="/assets/img/payment.png" alt="">
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="search_icon" tabindex="-1" aria-labelledby="search_icon" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <form action="#" class="nav-search" method="post">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..." />
                                    <button type="submit" class="input-group-text btn-info">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Optional JavaScript -->
        <script src="/assets/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/main.js"></script>
    </body>
</html>
