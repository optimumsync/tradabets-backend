@extends('_layouts.tradabet-home')

@section('main-content')

    <section>
        <div>

            <div>
                @guest
                    Spots book 
                @endguest

                @auth
                        <iframe src="{{$url}}" style="width:100%; height:calc(100vh - 101px); border:none; margin:0; padding:0; overflow:hidden; z-index:999999;"></iframe>
                @endauth
            </div>
        </div>
    </section>

@endsection
