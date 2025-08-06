
    @include('_includes/errors')

    @php
        $tab_class = 'tabs';
        $tab_class .= (isset($tab_position) && $tab_position == 'left') ? ' tabs-vertical tabs-left tabs-secondary' : '';

        $is_in_page = (isset($is_in_page)) ? $is_in_page : 0;
    @endphp

    <div class="{{ $tab_class }}">
        <ul class="nav nav-tabs">

            @foreach($tab_link_arr as $url => $title)
                @php
                    $url = (!$is_in_page && starts_with($url, '#')) ? '#' : $url;

                    $li_class = 'nav-item';
                    $li_class .= (isset($active) && strtolower($active) == strtolower($title)) ? ' active' : '';
                    $li_class .= (!$is_in_page && $url == '#' && !str_contains($li_class, 'active')) ? ' disabled' : '';
                @endphp

                <li class="{{ $li_class }}">
                    <a href="{{ $url }}" class="nav-link" {!! ($is_in_page ? 'data-toggle="tab"' : '') !!}>{{ $title }}</a>
                </li>

            @endforeach

        </ul>
        <div class="tab-content">
