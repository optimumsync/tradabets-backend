@extends('_layouts.tradabet-home')

@section('main-content')

<section>
	<div>

    <div class="slider-div">
        <div class="container container-template">
        <h3 class="slider-text">Next-Gen Online Gaming & Sports</h3>
        <h2 class="bonus-text">GET $50 Welcome Bonus</h2>
            <p class="slider-paragraph">Lorem Ipsum is simply dummy text of the printing and type-<br>
                -setting industry. Lorem Ipsum has been the industry's standard dummy text<br>
                when an unknown printer took a galley of type</p>
            <input type="button" class="btn btn-primary join-button" value="Join Now"/>

        </div></div>
        <div class="live-games-div">
            <div class="container container-template">
                <div class="col-lg-12 row live-games-row">
                    <div class="col-lg-9">
                        <div class="col-lg-12 row live-games-child-row">
                            <div class="col-lg-4">
                                <img src="/themes/admin/img/live-casino.png" alt="live-casino" />
                                <p class="live-games-paragraph"><span class="live-games-heading">LIVE Casino</span><br><a href="#" class="live-games-links">Know More</a></p>
                            </div>
                            <div class="col-lg-4">
                                <img src="/themes/admin/img/live-bet.png" alt="live-bet" />
                                <p class="live-games-paragraph">
                                @auth
                                    <a href="{{ 'bet' }}" >
                                        <span class="live-games-heading">LIVE Bet</span>
                                    </a>
                                @endauth

                                @guest
                                    <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Login to Bet">
                                        <span class="live-games-heading">LIVE Bet</span>
                                    </a>
                                @endguest
                                    <br><a href="#" class="live-games-links">Know More</a></p>
                            </div>
                            <div class="col-lg-4">
                                <img src="/themes/admin/img/live-poker.png" alt="live-poker" />
                                <p class="live-games-paragraph"><span class="live-games-heading">LIVE Poker</span><br><a href="#" class="live-games-links">Know More</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                       <h2 style="color:#000000;font-family: Poppins-Semi-Bold">Choose Variety of Games</h2>
                        <hr class="variety-games-underline">
                        <p>Lorem Ipsum is simply dummy text of the printing and type setting industry
                            It has been the industry's standard dummy text</p>
                    </div>
                </div>
                <div style="text-align: center">
                    <input type="button" class="btn btn-primary live-game-play-button" value="Play Games Now"/>
                </div>
            </div>
        </div>
        <div class="new-sports-event-div">
            <div class="container container-template">
                <div class="col-lg-12 row">
                    <div class="col-lg-6">
                        <div class="new-sports-events">
                        <div style="padding-top:30%">
                            <div class="new-sports-events-div"><p class="new-sports-events-paragraph">New Sports Events Available Now</p></div>
                            <p class="new-sports-event-description">
                            Lorem Ipsum is simply dummy text of the printing and type setting industry
                            It has been the industry's standard dummy text
                            </p>
                        </div>
                        </div>
                    </div>
                    <div class="col-lg-6 ">
                        <div class="new-casino-event">
                        <div style="padding-top:30%">
                        <div class="new-sports-events-div"><p class="new-sports-events-paragraph">Weekly Reload Casino, Play Now</p></div>
                            <p class="new-sports-event-description">Lorem Ipsum is simply dummy text of the printing and type setting industry
                            It has been the industry's standard dummy text
                        </p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="services-class">
            <div class="container container-template">
                <div style="text-align: center">
                    <h2 class="services-page-heading">Our Services</h2>
                    <hr class="services-underline">
                </div>
            <div class="col-lg-12 row">
                <div class="col-lg-3">
                    <div class="service-desc">
                        <p>
                        <img src="/themes/admin/img/services-icon.png" alt="live-casino" /></p>
                        <h4 class="service-header">Sports</h4>
                        <p>Up to $1000 Welcome Bonus<br>
                        & Over 450 Casino Games
                        </p>
                        <input type="button" class="btn btn-primary btn-bet-now" value="Bet Now">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="service-desc">
                        <p>
                            <img src="/themes/admin/img/services-icon.png" alt="live-casino" /></p>
                        <h4 class="service-header">Casino</h4>
                        <p>Up to $1000 Welcome Bonus<br>
                            & Over 450 Casino Games
                        </p>
                        <input type="button" class="btn btn-primary btn-bet-now" value="Bet Now">
                    </div>

                </div>
                <div class="col-lg-3">
                    <div class="service-desc">
                        <p>
                            <img src="/themes/admin/img/services-icon.png" alt="live-casino" /></p>
                        <h4 class="service-header">Poker</h4>
                        <p>Up to $1000 Welcome Bonus<br>
                            & Over 450 Casino Games
                        </p>
                        <input type="button" class="btn btn-primary btn-bet-now" value="Bet Now">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="service-desc">
                        <p>
                            <img src="/themes/admin/img/services-icon.png" alt="live-casino" /></p>
                        <h4 class="service-header">Games</h4>
                        <p>Up to $1000 Welcome Bonus<br>
                            & Over 450 Casino Games
                        </p>
                        <input type="button" class="btn btn-primary btn-bet-now" value="Bet Now">
                    </div>
                </div>
            </div>
            </div>
        </div>
        </div>
        	</section>

 @endsection

