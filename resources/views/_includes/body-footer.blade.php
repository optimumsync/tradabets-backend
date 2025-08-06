
                    </div>

                </div> <!-- /row -->
                <!-- end: page -->

            </section>
        </div>

    </section>


    {!! Form::open(['url' => '/logout', 'name' => 'logout_user_form']) !!}

    {!! Form::close() !!}


    {!! Form::open(['url' => '', 'name' => 'delete_form']) !!}

        @method('DELETE')

    {!! Form::close() !!}
