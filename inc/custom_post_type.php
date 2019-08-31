<?php
// カスタム投稿タイプの設定
add_action( 'init', 'create_post_type' );
function create_post_type() {
    $Supports = ['title','editor','revisions','cumstom-fields','thumbnail'];

    register_post_type( 'front', [ // 投稿タイプ名の定義
        'labels' => [
            'name'          => 'フロントページ', // 管理画面上で表示する投稿タイプ名
            'singular_name' => 'front',    // カスタム投稿の識別名
        ],
        'public'        => true,  // 投稿タイプをpublicにするか
        'has_archive'   => false, // アーカイブ機能ON/OFF
        'menu_position' => 5,     // 管理画面上での配置場所
        'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
        'supports' => $Supports
    ]);

     register_taxonomy(
       'front_news',  /* タクソノミーのslug */
       'front',        /* 属する投稿タイプ */
        array(
          'hierarchical' => true,
          'update_count_callback' => '_update_post_term_count',
          'label' => 'ニュース',
          'singular_label' => 'ニュース',
          'public' => true,
          'show_ui' => true
        )
        );

     register_taxonomy(
        'front_about',  /* タクソノミーのslug */
        'front',        /* 属する投稿タイプ */
        array(
          'hierarchical' => true,
          'update_count_callback' => '_update_post_term_count',
          'label' => 'MDの概要',
          'singular_label' => 'MDの概要',
          'public' => true,
          'show_ui' => true
        )
      );
    // register_post_type( 'news', [ // 投稿タイプ名の定義
    //     'labels' => [
    //         'name'          => 'ニュース', // 管理画面上で表示する投稿タイプ名
    //         'singular_name' => 'news',    // カスタム投稿の識別名
    //     ],
    //     'public'        => true,  // 投稿タイプをpublicにするか
    //     'has_archive'   => false, // アーカイブ機能ON/OFF
    //     'menu_position' => 5,     // 管理画面上での配置場所
    //     'show_in_rest'  => true,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
    //     'supports' => $Supports
    // ]);
}

// 作成したカスタム投稿タイプにカスタムフィールドを追加
add_action('admin_menu', 'add_custom_fields');
function add_custom_fields(){
    add_meta_box(
        'on-off-button', //編集画面セクションのHTML ID
        '投稿のオン・オフ', //編集画面セクションのタイトル、画面上に表示される
        'insertOnOffButton', //編集画面セクションにHTML出力する関数
        'post', //投稿タイプ名
        'normal' //編集画面セクションが表示される部分
    );
    add_meta_box(
        'on-off-button', //編集画面セクションのHTML ID
        '投稿のオン・オフ', //編集画面セクションのタイトル、画面上に表示される
        'insertOnOffButton', //編集画面セクションにHTML出力する関数
        'front', //投稿タイプ名
        'normal' //編集画面セクションが表示される部分
    );
}
// カスタムフィールドの入力エリア
function insertOnOffButton() {
	global $post;
  
  $options = array('OK','NG');
  $n       = count($options);

  $on_off_radio_field = get_post_meta($post->ID, 'on_off_radio_field',true);
  echo '<label for="radio_field">ONにチェックが入った記事のみが表示されます。</label><br>';
  for ($i=0; $i<$n; $i++) {
	  $option = $options[$i];
	  if ($option==$on_off_radio_field) {
      echo '<input type="radio" name="on_off_radio_field" value="'. esc_html($option) .'" checked > '. $option .' ';
	  } else {
      echo '<input type="radio" name="on_off_radio_field" value="'. esc_html($option) .'" > '. $option .' ';
    }
  }

// カスタムフィールドの保存（新規・更新・削除）
function save_my_custom_fields( $post_id ) {
  $mydata = $_POST['on_off_radio_field']; // input>name
  $field_value = get_post_meta($post_id, 'on_off_radio_field', true);
  if ($field_value == "")
    add_post_meta($post_id, 'on_off_radio_field', $mydata, true);
  elseif($mydata != $field_value)
    update_post_meta($post_id, 'on_off_radio_field', $mydata);
  elseif($mydata=="")
    delete_post_meta($post_id, 'on_off_radio_field', $field_value);
}

	// if( get_post_meta($post->ID,'book_label',true) == "is-on" ) {
	// 	$book_label_check = "checked";
	// }//チェックされていたらチェックボックスの$book_label_checkの場所にcheckedを挿入
	// echo 'ベストセラーラベル： <input type="checkbox" name="book_label" value="is-on" '.$book_label_check.' ><br>';
}

//画像をアップする場合は、multipart/form-dataの設定が必要なので、post_edit_form_tagをフックしてformタグに追加
add_action('post_edit_form_tag', 'custom_metabox_edit_form_tag');
function custom_metabox_edit_form_tag(){
  echo ' enctype="multipart/form-data"';
}

// カスタム投稿をフロントに表示する
if ( ! function_exists( 'bulma_get_archive_custom_posts' ) ) {
  function bulma_get_archive_custom_posts( $taxonomy_name = 'front_about',$post_type= 'front') 
  {
    $args = array(
        'orderby' => 'name',
        'hierarchical' => false
    );
    $taxonomys = get_terms( $taxonomy_name, $args);
    if(!is_wp_error($taxonomys) && count($taxonomys)) {
      foreach($taxonomys as $taxonomy) {
        $url_taxonomy = get_term_link($taxonomy->slug, $taxonomy_name);
        $tax_get_array = array(
            'post_type' => $post_type, //表示したいカスタム投稿
            'posts_per_page' => 5,//表示件数
            // https://blog.nakachon.com/2014/10/27/dont-use-name-field-tax-query-in-japanese/
            // termsにはidを, fieldにはterm_idを入れるべき
            'tax_query' => array(
                array(
                      'taxonomy' => $taxonomy_name,
                      'terms'     => array($taxonomy -> term_id),
                      'field'    => 'term_id',
                      'operator' => 'IN',
                      'include_children' => true
                     )
            )
        );
        $tax_posts = get_posts( $tax_get_array );
        // ポストが存在するならば
        if($tax_posts):
          echo  '<section class="front-section">';
        //  echo  '<div class="column is-2">';
        //  echo    '<span class="front-icon">'.shard_fontawesome_random($taxonomy->term_id).'</span>'; // アイコンをtermi_idを元にしてランダムに生成する
        //  echo  '</div>';
          echo    '<h2 class="title is-3 front-section__heading" id="' . esc_html($taxonomy->slug) . '">';
          //echo      '<a href="'. $url_taxonomy .'">'. esc_html($taxonomy->name) .'</a>';
          echo    esc_html($taxonomy->name);
          echo    '</h2>';
          echo    '<div class="front-section__content">';
            foreach($tax_posts as $tax_post):
               echo '<article class="front-section__article container">';
               // echo '<h3 class="front-listItem"><a href="'. get_permalink($tax_post->ID).'">'. get_the_title($tax_post->ID).'</a></h3>';
               $custom_post = get_post($tax_post->ID);
               echo '<h3 class="title is-3 front-section__article__title">'. get_the_title($tax_post->ID).'</h3>';
               echo '<div class="columns">';
               echo   '<div class="column is-8">'.$custom_post->post_content.'</div>';
               echo   '<div class="column is-4">' .get_the_post_thumbnail( $tax_post->ID , 'medium' ).'</div>';
               echo '</div>';
               echo '<a class="button" href="'.get_the_permalink($tax_post->ID).'">記事の詳細</a>';
               echo '</article>';
            endforeach;
            wp_reset_postdata();
          echo    '</div>';
          echo  '</section>';
        endif;
      } // end foreach
    } // end if
  } // end function
} // end exist


if ( ! function_exists( 'get_bulma_archive_custom_posts' ) )
{
  function get_bulma_archive_custom_posts( $taxonomy_name = 'front_about',$post_type= 'front') 
  {
    $args = array(
        'orderby' => 'name',
        'hierarchical' => false
    );
    $taxonomys = get_terms( $taxonomy_name, $args);

    if(!is_wp_error($taxonomys) && count($taxonomys))
    {
      foreach($taxonomys as $taxonomy)
      {
        $url_taxonomy = get_term_link($taxonomy->slug, $taxonomy_name);
        $tax_get_array = array(
            'post_type' => $post_type, //表示したいカスタム投稿
            'posts_per_page' => 5,//表示件数
            // https://blog.nakachon.com/2014/10/27/dont-use-name-field-tax-query-in-japanese/
            // termsにはidを, fieldにはterm_idを入れるべき
            'tax_query' => array(
              array(
                      'taxonomy' => $taxonomy_name,
                      'terms'     => array($taxonomy -> term_id),
                      'field'    => 'term_id',
                      'operator' => 'IN',
                      'include_children' => true
              )
            )
        );
        $tax_posts = get_posts( $tax_get_array );
      }
      return $tax_posts;
    }
    return 0;
  }
}