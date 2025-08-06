@extends('_layouts.tradabet-home')

@section('main-content')

<section>

    <iframe id="myiframe" src="{{$iframe_url}}" style="width:100%; height:calc(100vh - 101px); border:none; margin:0; padding:0; overflow:hidden; z-index:999999;"></iframe>
</section>

<script>
    var ifr = document.getElementById('myiframe');
    if (ifr) {
        ifr.attr("src", "{{$iframe_url}}");
    }

</script>

 @endsection

