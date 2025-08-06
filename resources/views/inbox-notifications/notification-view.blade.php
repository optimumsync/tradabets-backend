@extends('_layouts.master')

@section('main-title', 'Inbox')

@section('main-content')

    <section class="card">
        <header class="card-header">

            @include('_components/pages/card-title', [
                    'title' => 'View',
                    'description' => $notification->subject,
                ])

        </header>
        <div class="card-body">

            <div class="row">
                <div class="col-md-6 col-lg-6">

                    @include('_components.form-elements.text-view-md6', ['label' => 'Subject', 'value' => $notification->subject])

                    @include('_components.form-elements.text-view-md6', ['label' => 'Body', 'value' => $notification->body])

                    @include('_components.form-elements.text-view-md6', ['label' => 'Date', 'value' => $notification->created_at])


                </div>
            </div> <!-- /row -->

            @include('_components/tabs/btm')

        </div>
        <footer class="card-footer">

            <div class="row">
                <div class="col-md-6">
                    <a href="/inbox" class="btn btn-primary">Back</a>
                </div>
                <div class="col-md-6 text-right">

                </div>
            </div> <!-- /row -->

        </footer>
    </section>

@endsection
