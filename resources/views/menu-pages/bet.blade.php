@extends('_layouts.tradabet-home')

@section('main-content')

    <section>
        <div>

            <div>
                <!-- <iframe src="https://app-dev01.sparket.dev/integration-app/tradabets?user_id={{Auth::user()->id}}&login_token={{Auth::user()->token}}&balance={{$balance}}" style="width:100%; height:800px; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;"></iframe> -->

                <iframe src="https://www.sparket.app/integration-app/tradabets?user_id={{Auth::user()->id}}&login_token={{Auth::user()->token}}&balance={{$balance}}" style="width:100%; height:800px; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;"></iframe>
            </div>
        </div>
    </section>

@endsection

