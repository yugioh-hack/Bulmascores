<?php
// カスタム投稿タイプの設定
add_action( 'init', 'create_post_type' );
function create_post_type() {
    $Supports = ['title','editor','revisions','cumstom-fields','thumbnail','excerpt'];

    // フロントのMD概要
    register_post_type( 'front', [ // 投稿タイプ名の定義
        'labels' => [
            'name'          => 'フロントページ', // 管理画面上で表示する投稿タイプ名
            'singular_name' => 'front',    // カスタム投稿の識別名
        ],
        'public'        => true,  // 投稿タイプをpublicにするか
        'has_archive'   => false, // アーカイブ機能ON/OFF
        'menu_position' => 5,     // 管理画面上での配置場所
        'show_in_rest'  => false,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
        'supports' => $Supports,
        'menu_icon'     => 'dashicons-edit'
    ]);

    // 地図
    register_post_type( 'map', [ // 投稿タイプ名の定義
        'labels' => [
            'name'          => 'マップ', // 管理画面上で表示する投稿タイプ名
            'singular_name' => 'map',    // カスタム投稿の識別名
        ],
        'public'        => true,  // 投稿タイプをpublicにするか
        'has_archive'   => false, // アーカイブ機能ON/OFF
        'menu_position' => 5,     // 管理画面上での配置場所
        'show_in_rest'  => false,  // 5系から出てきた新エディタ「Gutenberg」を有効にする
        'supports' => $Supports,
        'menu_icon'     => 'dashicons-location-alt'
    ]);

     register_taxonomy(
        'front_about',  /* タクソノミーのslug */
        'front',        /* 属する投稿タイプ */
        array(
          'hierarchical' => true,
          'update_count_callback' => '_update_post_term_count',
          'label' => 'MDの概要カテゴリー',
          'singular_label' => 'MDの概要カテゴリー',
          'public' => true,
          'show_ui' => true
        )
      );

     register_taxonomy(
        'map-cat',  /* タクソノミーのslug */
        'map',        /* 属する投稿タイプ */
        array(
          'hierarchical' => true,
          'update_count_callback' => '_update_post_term_count',
          'label' => '地図',
          'singular_label' => '地図',
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

//画像をアップする場合は、multipart/form-dataの設定が必要なので、post_edit_form_tagをフックしてformタグに追加
add_action('post_edit_form_tag', 'custom_metabox_edit_form_tag');
function custom_metabox_edit_form_tag(){
  echo ' enctype="multipart/form-data"';
}

// カスタム投稿をフロントに表示する-third
if ( ! function_exists( 'bulma_get_front_custom_posts_3' ) ) {
  function bulma_get_front_custom_posts_3( $taxonomy_name = 'front_about',$post_type= 'front') 
  {
    $args = array(
        'orderby' => 'name',
        'hierarchical' => false
    );
    $taxonomys = get_terms( $taxonomy_name, $args);
    // 指定したタクソノミーとその記事が存在する場合
    if(!is_wp_error($taxonomys) && count($taxonomys)) {
      foreach($taxonomys as $taxonomy) {
        $url_taxonomy = get_term_link($taxonomy->slug, $taxonomy_name);
        $tax_get_array = array(
            'post_type' => $post_type, //表示したいカスタム投稿
            'posts_per_page' => 3,//表示件数
            // https://blog.nakachon.com/2014/10/27/dont-use-name-field-tax-query-in-japanese/
            // termsにはidを, fieldにはterm_idを入れるべき
            'tax_query' => array(
                array(
                      'taxonomy' => $taxonomy_name,
                      'terms'     => array($taxonomy -> term_id),
                      'field'    => 'term_id',
                      'operator' => 'IN',
                      'include_children' => true,
                     )
            ),
            // カスタムフィールドで表示のONOFF判定
            'meta_query'  => array (
              array(
                'key'   => 'on_off',
                'value' => 'OK'
              )
            )
        );
        $tax_posts = get_posts( $tax_get_array );
        // ポストが存在するならば
        if($tax_posts):
          $current_post = 1;
          echo  '<section class="front-section">';
          echo    '<h2 class="title is-3 front-section__heading" id="' . esc_html($taxonomy->slug) . '">';
          //echo      '<a href="'. $url_taxonomy .'">'. esc_html($taxonomy->name) .'</a>';
          echo    esc_html($taxonomy->name);
          echo    '</h2>';
          echo    '<div class="front-section__content columns is-gapless">';
            foreach($tax_posts as $tax_post):
               $custom_post = get_post($tax_post->ID);
               $custom_excerpt = strip_shortcodes($custom_post->post_excerpt); 
               if( get_the_post_thumbnail($tax_post->ID , 'full') ) {
                $custom_thumbnail = get_the_post_thumbnail($tax_post->ID , 'bulmascores_square');
               }else{
                $custom_thumbnail = get_template_directory_uri(). '/assets/img/daitai_cat.jpg';
               }
               echo '<div class="front-section__container--about2">';
               //echo '  <div class="front-section__container--about2__img hexclip">'.$custom_thumbnail.'</div>';
               echo '  <div class="front-section__container--about2__img">'.$custom_thumbnail.'</div>';
               echo '  <article class="front-section__article--about2">';
               echo '    <div class="front-section__article--about2__contents is-title">';
               echo '    <a class="front-section__article--about2__link" href="'. get_permalink($tax_post->ID).'">';
               echo '    </a>';
               echo '    </div>';
               echo '    <div class="front-section__article--about2__contents is-post">';
               echo '    <a class="front-section__article--about2__link" href="'. get_permalink($tax_post->ID).'">';
               echo '      <div class="front-section__article--about2__post">';
               echo '      <h3 class="front-section__article--about2__catchtitle">'.get_the_title($tax_post->ID).'</h3>';
               // echo '      <h4 class="front-section__article--about__catchtitle">'.esc_html( get_post_meta($tax_post->ID, 'key_catch', true) ).'</h4>';
               echo '      <p>' .$custom_excerpt. '</p>';
               echo '      </div>';
               echo '    </a>';
               echo '    </div>';
               // echo '      <div class="front-section__article--about__link"><a class="button" href="'. get_permalink($tax_post->ID).'">詳細を表示</a></div>'; 
               echo '  </article>';
               echo '</a>';
               echo '</div>';
               $current_post++;
            endforeach;
            wp_reset_postdata();
          echo    '</div>';
          echo  '</section>';
        endif;
      } // end foreach
    } // end if
  } // end function
} // end exist

// カスタム投稿をフロントに表示する-second
if ( ! function_exists( 'bulma_get_front_custom_posts_2' ) ) {
  function bulma_get_front_custom_posts_2( $taxonomy_name = 'front_about',$post_type= 'front') 
  {
    $args = array(
        'orderby' => 'name',
        'hierarchical' => false
    );
    $taxonomys = get_terms( $taxonomy_name, $args);
    // 指定したタクソノミーとその記事が存在する場合
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
                      'include_children' => true,
                     )
            ),
            // カスタムフィールドで表示のONOFF判定
            'meta_query'  => array (
              array(
                'key'   => 'on_off',
                'value' => 'OK'
              )
            )
        );
        $tax_posts = get_posts( $tax_get_array );
        // ポストが存在するならば
        if($tax_posts):
          $current_post = 1;
          echo  '<section class="front-section">';
        //  echo  '<div class="column is-2">';
        //  echo    '<span class="front-icon">'.shard_fontawesome_random($taxonomy->term_id).'</span>'; // アイコンをtermi_idを元にしてランダムに生成する
        //  echo  '</div>';
          echo    '<h2 class="title is-3 front-section__heading" id="' . esc_html($taxonomy->slug) . '">';
          //echo      '<a href="'. $url_taxonomy .'">'. esc_html($taxonomy->name) .'</a>';
          echo    esc_html($taxonomy->name);
          echo    '</h2>';
          // echo    '<div class="front-section__content">';
            foreach($tax_posts as $tax_post):
               $custom_post = get_post($tax_post->ID);
               $custom_excerpt = strip_shortcodes($custom_post->post_excerpt); 
               if( get_the_post_thumbnail($tax_post->ID , 'full') ) {
                $custom_thumbnail = get_the_post_thumbnail($tax_post->ID , 'full');
               }else{
                $custom_thumbnail = get_template_directory_uri(). '/assets/img/daitai_cat.jpg';
               }
               // echo '<article class="front-section__article container">';
               echo '<div class="front-section__container--about container">';
               echo '  <div class="front-section__container--about__img">'.$custom_thumbnail.'</div>';
               if($current_post%2 != 0) {
               echo '  <article class="front-section__article--about columns is-mobile">';
               }else{
               echo '  <article class="front-section__article--about is-reverse columns is-mobile">';
               }
               echo '    <div class="front-section__article--about__contents is-title column">';
               echo '    <a class="front-section__article--about__link" href="'. get_permalink($tax_post->ID).'">';
               // echo '      <h3 class="front-section__article--about__title">'.$current_post.'</h3>';
               //echo '      <h3 class="front-section__article--about__title">'. get_the_title($tax_post->ID).'</h3>';
               echo '    </a>';
               echo '    </div>';
               echo '    <div class="front-section__article--about__contents is-post column">';
               echo '    <a class="front-section__article--about__link" href="'. get_permalink($tax_post->ID).'">';
               echo '      <div class="front-section__article--about__post">';
               echo '      <h3 class="front-section__article--about__catchtitle">'.get_the_title($tax_post->ID).'</h3>';
               // echo '      <h4 class="front-section__article--about__catchtitle">'.esc_html( get_post_meta($tax_post->ID, 'key_catch', true) ).'</h4>';
               echo '      <p>' .$custom_excerpt. '</p>';
               echo '      </div>';
               echo '    </a>';
               echo '    </div>';
               // echo '      <div class="front-section__article--about__link"><a class="button" href="'. get_permalink($tax_post->ID).'">詳細を表示</a></div>'; 
               echo '  </article>';
               echo '</a>';
               echo '</div>';
               $current_post++;
            endforeach;
            wp_reset_postdata();
          // echo    '</div>';
          echo  '</section>';
        endif;
      } // end foreach
    } // end if
  } // end function
} // end exist
// カスタム投稿をフロントに表示する
if ( ! function_exists( 'bulma_get_front_custom_posts' ) ) {
  function bulma_get_front_custom_posts( $taxonomy_name = 'front_about',$post_type= 'front') 
  {
    $args = array(
        'orderby' => 'name',
        'hierarchical' => false
    );
    $taxonomys = get_terms( $taxonomy_name, $args);
    // 指定したタクソノミーとその記事が存在する場合
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
                      'include_children' => true,
                     )
            ),
            // カスタムフィールドで表示のONOFF判定
            'meta_query'  => array (
              array(
                'key'   => 'on_off',
                'value' => 'OK'
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

// フロントのマップ等
if ( ! function_exists( 'bulma_get_archive_custom_posts' ) ) {
  function bulma_get_archive_custom_posts( $taxonomy_name = 'map-cat',$post_type= 'map') 
  {
    $args = array(
        'orderby' => 'name',
        'hierarchical' => false
    );
    $taxonomys = get_terms( $taxonomy_name, $args);
    // 指定したタクソノミーとその記事が存在する場合
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
                      'include_children' => true,
                     )
            ),
            // カスタムフィールドで表示のONOFF判定
            'meta_query'  => array (
              array(
                'key'   => 'on_off',
                'value' => 'OK'
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
              // MAPは記事の内容やタイトルを直接出力しない
               echo '<article class="front-section__article container">';
               // echo '<h3 class="front-listItem"><a href="'. get_permalink($tax_post->ID).'">'. get_the_title($tax_post->ID).'</a></h3>';
               // $custom_post = get_post($tax_post->ID);
               echo '<h3 class="screen-reader-text">'. get_the_title($tax_post->ID).'</h3>';
               echo '<div class="columns">';
               echo   '<div class="column">';
                      bulma_map_meta_data($tax_post->ID);
               echo   '</div>';
               echo   '<div class="column is-three-quaters">';
                       bulma_map_osm_data($tax_post->ID);
               echo   '</div>';
               echo '</div>';
               // echo '<a class="button" href="'.get_the_permalink($tax_post->ID).'">記事の詳細</a>';
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

if(!function_exists('bulma_map_meta_data')) {
  function bulma_map_meta_data($tax_post_id){
    global $post;
    global $date_keymaps;

    $keymaps = array();
    $keymaps = $date_keymaps;

    echo '<dl class="front-map--meta">';
      foreach ($keymaps as $key => $val) {
        $key_name = esc_html( get_post_meta($tax_post_id, $val['key_name'],true) );
        $key_public_name = $val['key_public_name']; 
        if(isset($key_name)) {  
          echo '<dt class="front-map--meta__dt">'.$key_public_name.'<dt>';
          echo '<dd class="front-map--meta__dd">';
            if($val['key_name'] != 'key_twitter') { // Twitterかどうかで条件分岐 
              echo $key_name;
            }else{
              echo '<a href="//twitter.com/'.$key_name.'">@' .$key_name. '</a>';
            }
          echo '</dd>'; 
        }
      }
    echo '</dl>';
  }
}

if(!function_exists('bulma_map_osm_data')) {
  function bulma_map_osm_data($tax_post_id){
    global $post;
    global $date_mappings;

    $maps = array();
    $maps    = $date_mappings;
    foreach ($maps as $keylabel => $val) {
      echo get_post_meta($tax_post_id, $val['key_name'],true); 
    }
  }
}