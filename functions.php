<?php
/* グーテンベルグのCSSをテーマ側で読み込み */
function mytheme_setup(){
	//theme.min.cssの有効化
	add_theme_support('wp-block-styles');

	//縦横比を維持したレスポンシブを有効化
	add_theme_support('responsive-embeds');

	//editor-style.cssを有効化&読み込み
	add_theme_support('editor-styles');
	add_editor_style('editor-style.css');

	//ページタイトルを有効化
	add_theme_support('title-tag');

	//link,style,scriptのHTML5を有効化
	add_theme_support('html5',array(
		'style',
		'script'
	));
	//アイキャッチ画像の有効化
	add_theme_support('post-thumbnails');
}
add_action('after_setup_theme','mytheme_setup');


/* 投稿ラベルを”お知らせ”に修正 */
function Change_menulabel() {
	global $menu;
	global $submenu;
	$name = 'NEWS';
	$menu[5][0] = $name;
	$submenu['edit.php'][5][0] = '投稿一覧';
	$submenu['edit.php'][10][0] = '新規投稿';
}
function Change_objectlabel() {
	global $wp_post_types;
	$name = 'NEWS';
	$labels = &$wp_post_types['post']->labels;
	$labels->name = $name;
	$labels->singular_name = $name;
	$labels->add_new = _x('追加', $name);
	$labels->add_new_item = $name.'の新規追加';
	$labels->edit_item = $name.'の編集';
	$labels->new_item = '新規'.$name;
	$labels->view_item = $name.'を表示';
	$labels->search_items = $name.'を検索';
	$labels->not_found = $name.'が見つかりませんでした';
	$labels->not_found_in_trash = 'ゴミ箱に'.$name.'は見つかりませんでした';
}
add_action( 'init', 'Change_objectlabel' );
add_action( 'admin_menu', 'Change_menulabel' );


// /* カスタム投稿タイプの追加 */
// function cpt_register_news() { //add_actionの２つのパラメーターを定義
// 	$labels = [
// 		"singular_name" => "news",
// 		"edit_item" => "news",
// 	];
// 	$args = [
// 		"label" => "お知らせ", //管理画面、アーカイブページのタイトル、パンクズの名前に反映される！
// 		"labels" => $labels,
// 		"description" => "",
// 		"public" => true,
// 		"show_in_rest" => true,
// 		"rest_base" => "",
// 		"rest_controller_class" => "WP_REST_Posts_Controller",
// 		"has_archive" => true,
// 		"delete_with_user" => false,
// 		"exclude_from_search" => false,
// 		"map_meta_cap" => true,
// 		"hierarchical" => true,
// 		"rewrite" => [ "slug" => "news", "with_front" => true ], //スラッグをworksに設定
// 		"query_var" => true,
// 		"menu_position" => 5,
// 		"supports" => [ "title", "editor", "thumbnail" ],
// 	];
// 	register_post_type( "news", $args );
// }
// add_action( 'init', 'cpt_register_news' );

/* archive.phpの設定 */
function post_has_archive($args,$post_type){ //設定後に必ずパーマリンクを設定すること
    if('post' == $post_type){
        $args['rewrite'] = true;
        $args['has_archive'] = 'news-archive';//アーカイブページのurlを定義
        $args['label'] = 'NEWS'; //管理画面の投稿ラベル名をブログに変換
    }
    return $args;
}
add_filter('register_post_type_args','post_has_archive',10,2);

// アーカイブページ 投稿表示件数の設定
function custom_posts_per_page($query) {
    if(is_admin() || ! $query->is_main_query()){
        return;
    }
    // 制作実績
    if($query->is_archive('works')) {
        $query->set('posts_per_page', '5'); //ここで表示件数を変更
    }
}
add_action('pre_get_posts', 'custom_posts_per_page');


//管理画面にウィジェットエリアを追加
/* サイドバー */
//管理画面にウィジェットエリアを追加
function widgetarea_init() {
	register_sidebar( array(
		'name'          => 'Sidebar',
		'id'            => 'sidebar-1',
		'description'   => 'サイドバー1の説明を入れます。',
    'class'         => 's1',
	  'before_widget' => '<div id="%1$s" class="widget %2$s">',
  'after_widget' => '</div>',
  'before_title' => '<h2 class="widget_title">',
  'after_title' => '</h2>',
	) );

	 register_sidebar( array(
        'name'          => 'Sidebar2',
        'id'            => 'sidebar-2',
        'description'   => 'サイドバー2の説明を入れます。',
        'class'         => 's2',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget_title">',
        'after_title' => '</h2>',
    ));
}
add_action( 'widgets_init', 'widgetarea_init' );

//Breadcrumb NavXTのパンくずで”投稿一覧”を追加（ここでは”NEWS”で表示）
function bcn_add($bcnObj) {
	// デフォルト投稿のアーカイブの場合、TOP＞投稿一覧という形で追加
	if (is_post_type_archive('post')) {
        	// 新規のtrailオブジェクトを末尾に追加する
		$bcnObj->add(new bcn_breadcrumb('news', null, array('archive', 'post-clumn-archive', 'current-item')));
		// trailオブジェクト0とtrailオブジェクト1の中身を入れ替える
		$trail_tmp = clone $bcnObj->trail[1];
		$bcnObj->trail[1] = clone $bcnObj->trail[0];
        $bcnObj->trail[0] = $trail_tmp;
    // デフォルト投稿の詳細ページの場合、TOP > 投稿一覧 > カテゴリー1 >（投稿タイトル）で表示
	}elseif (is_singular('post')) {
        // 新規のtrailオブジェクトを追加する
        $bcnObj->add(new bcn_breadcrumb('news', null, array('post-clumn-archive'), home_url('news-archive'), null, true));
		$trail_tmp = clone $bcnObj->trail[3];	//配列の最後（一番左）に追加
		$bcnObj->trail[3] = clone $bcnObj->trail[2]; //配列の最後から2番目に追加
		$bcnObj->trail[2] = $trail_tmp; //配列の最後から2番にあった値を最後（一番左に追加）
}
	return $bcnObj;
}
add_action('bcn_after_fill', 'bcn_add');

//アーカイブのタイトルから「アーカイブ：」を消すカスタマイズ
function custom_archive_title($title){
    $titleParts=explode(': ',$title);
    if($titleParts[1]){
        return $titleParts[1];
    }
    return $title;
}
add_filter('get_the_archive_title','custom_archive_title');