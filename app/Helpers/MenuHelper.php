<?php

namespace App\Helpers;

use Illuminate\Support\Arr;

class MenuHelper
{
    private static $html = '';
    private static $setting_arr = [];
    private static $current_row = [];
    private static $menu_level = 1;
    private static $request_path_arr = [];
    private static $request_group = null;
    private static $request_controller = null;
    private static $request_action = null;


    /**
     * Create a menu
     *
     * @return string
     */
    public static function create_menu($link_arr, $setting_arr=null)
    {
        self::$setting_arr = $setting_arr;

        // parse
        $request_path = request()->path();
        $request_path_arr = explode('/', $request_path.'/');
        $request_path_arr = array_filter(array_map('trim', $request_path_arr));

        self::$request_path_arr = $request_path_arr;
        self::$request_group = (isset($request_path_arr[0]) ? strtolower(trim($request_path_arr[0], '/')) : null);
        self::$request_controller = (isset($request_path_arr[1]) ? strtolower(trim($request_path_arr[1], '/')) : self::$request_group);



        self::walk_link_array($link_arr);

        return self::$html;
    }

    /**
     * Loop the array
     *
     * @return void
     */
    private static function walk_link_array($link_arr, $level=1)
    {
        self::$html .= self::open_ul($level);

        // loop
        foreach($link_arr as $row){
            self::$current_row = $row;



            /*if(!can_user_access_this_group($row['uri'])){

                continue;
            }*/

            self::$html .= self::open_li($level);

                self::$html .= self::create_link($level);

                if(isset($row['sub-links'])){
                    self::walk_link_array($row['sub-links'], ($level+1));
                }

            self::$html .= self::close_li();
        }

        self::$html .= self::close_ul();
    }

    /**
     * Opens an <ul>
     *
     * @return void
     */
    private static function open_ul($level)
    {
        $ul_setting_arr = ($level == 1) ? self::$setting_arr['top-level']['ul'] : self::$setting_arr['sub-level']['ul'];

        $class_arr = [];
        $class_arr[] = (isset($ul_setting_arr['class'])) ? $ul_setting_arr['class'] : '';

        $props = self::parse_element_class_properties($class_arr);

        self::$html .= ($props) ? '<ul '.$props.'>' : '<ul>';
    }

    /**
     * Closes an <ul>
     *
     * @return void
     */
    private static function close_ul()
    {
        self::$html .= '</ul>';
    }

    /**
     * Creates a <li>
     *
     * @return void
     */
    private static function open_li($level)
    {
        $li_setting_arr = ($level == 1) ? self::$setting_arr['top-level']['li'] : self::$setting_arr['sub-level']['li'];
        $row = self::$current_row;

        $is_parent = self::is_li_parent($level);

        $trimmed_uri = strtolower(trim($row['uri'], '/'));
        //echo $trimmed_uri;

        $class_arr = [];
        $class_arr[] = (isset($li_setting_arr['class'])) ? $li_setting_arr['class'] : '';
        $class_arr[] = (isset($row['sub-links']) && isset($li_setting_arr['has-children-class'])) ? $li_setting_arr['has-children-class'] : '';

        $class_arr[] = ($is_parent) ? 'nav-expanded nav-active' : '';

        $props = self::parse_element_class_properties($class_arr);

        self::$html .= ($props) ? '<li '.$props.'>' : '<li>';
    }

	/**
	 * Check if <li> is parent.
	 *
	 * @param $level
	 * @return boolean
	 */
    private static function is_li_parent($level)
    {
        $row = self::$current_row;

		$row['uri'] = trim($row['uri'], '/');

        $uri_arr = (isset($row['sub-links'])) ? array_merge([str_replace('&&', '', $row['uri'])], Arr::pluck($row['sub-links'], 'uri')) : [$row['uri']];

        $uri_arr = array_map(function($val){
                    return strtolower(trim($val, '/'));
                }, $uri_arr
            );

		if($level <= 2){
			$is_parent = (in_array(self::$request_controller, $uri_arr)) ? true : false;
			$is_parent = (!$is_parent && in_array(self::$request_group, $uri_arr)) ? true : $is_parent;
			$is_parent = (!$is_parent && in_array(self::$request_group.'/'.self::$request_controller, $uri_arr)) ? true : $is_parent;
		}else{
			$is_parent = (self::$request_group == $row['uri'] || self::$request_group.'/'.self::$request_controller == $row['uri']) ? true : false;
		}


        return $is_parent;
    }

    /**
     * Closes a </li>
     *
     * @return void
     */
    private static function close_li()
    {
        self::$html .= '</li>';
    }

    /**
     * Creates a link
     *
     * @return void
     */
    private static function create_link($level)
    {
        $a_setting_arr = ($level == 1) ? self::$setting_arr['top-level']['a'] : self::$setting_arr['sub-level']['a'];
        $row = self::$current_row;

        $uri = (isset($row['uri']) && !isset($row['sub-links'])) ? $row['uri'] : '#';

        $class_arr = [];
        $class_arr[] = (isset($a_setting_arr['class'])) ? $a_setting_arr['class'] : '';
        $class_arr[] = (isset($row['a-class'])) ? $row['a-class'] : '';

        $props = self::parse_element_class_properties($class_arr);

        if(isset($row['session-badge'])){
			$session_badge_value = session($row['session-badge']['session-var'], '');

			$row['session-badge'] = ($session_badge_value) ? str_replace('[val]', $session_badge_value, $row['session-badge']['badge-html']) : null;
		}

        self::$html .= ($props) ? '<a href="'.$uri.'" '.$props.'>' : '<a href="">';
            self::$html .= (isset($row['prepend-title'])) ? $row['prepend-title'] : '';
            self::$html .= (isset($row['title'])) ? $row['title'] : '[no title]';
            self::$html .= (isset($row['session-badge']) && $row['session-badge']) ? $row['session-badge'] : '';
        self::$html .= '</a>';
    }

    /**
     * Parse element class properties
     *
     * @return string
     */
    private static function parse_element_class_properties($class_arr)
    {
        $class_arr = array_filter(array_map('trim', $class_arr));

        $prop_arr = [];
        $prop_arr[] = (count($class_arr)) ? 'class="'.implode(' ', $class_arr).'"' : '';

        $prop_arr = array_filter(array_map('trim', $prop_arr));

        $props = (count($prop_arr)) ? implode(' ', $prop_arr) : '';

        return $props;
    }
}
