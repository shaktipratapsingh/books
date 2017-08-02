<?php 
/*
Plugin Name: Books
Description: Displays Books
Author: Shakti
*/

//Register Book Post Type 
add_action( 'init', 'create_sh_book_type' );
function create_sh_book_type() {
	register_post_type( 'sh_books',
		array(
			'labels' => array(
				'name' => __( 'Books' ),
				'singular_name' => __( 'Book' )
			),
		'public' => true,
		'has_archive' => true,
		'supports' => array('title','editor','thumbnail')
		)
	);
}

//Add Book Categories author
add_action( 'init', 'sh_book_taxonomies', 0 );
function sh_book_taxonomies(){
register_taxonomy('sh_book_category_author', 'sh_books',
 array(
 'hierarchical'=>true,
 'label'=>'Author')
 );
}

//Add Book Categories publisher
add_action( 'init', 'sh_book_taxonomies_new', 0 );
function sh_book_taxonomies_new(){
register_taxonomy('sh_book_category_publisher', 'sh_books',
 array(
 'hierarchical'=>true,
 'label'=>'Publisher')
 );
}

add_action( 'add_meta_boxes', 'sh_book_extra_box' );
add_action( 'save_post', 'sh_books_save_postdata' );

function sh_book_extra_box() {
    add_meta_box(
        'sh_bk_info_box',
        __( 'Book Information', 'sh_bk_info_box' ), 
        'sh_bk_info_box',
        'sh_books',
	'side'
    );
}

/* Prints the box content */
function sh_bk_info_box( $post ) {

	$price = get_post_meta( $post->ID, 'price', true );
	$description =  get_post_meta( $post->ID, 'description', true );
	$star_rating =  get_post_meta( $post->ID, 'star_rating', true );
	
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'sh_book_noncename' );
	?>
<p>
  <label for="price">Price
	<div id="slider">
	<div id="custom-handle" class="ui-slider-handle"></div>
	</div>
   </label>
  
</p>
<input type="hidden" name="price"
   id="price" size="10" value="<?php echo $price; ?>" />
<p>
  <label for="description">Description
  <input type="text" name="description"
   id="description" size="10" value="<?php echo $description; ?>" />
  </label>
</p>

<p>
  <label for="star_rating">Star Rating:</label>
  <input type="text" id="star_rating" name="star_rating" readonly style="border:0; color:#f6931f; font-weight:bold;" value="<?php echo $star_rating; ?>">
</p>
 
<div id="slider-range"></div>
  <script>
  jQuery( function() {
    jQuery( "#slider-range" ).slider({
      range: false,
      max: 5,
      values: [ jQuery( "#star_rating" ).val() ],
      slide: function( event, ui ) {
        jQuery( "#star_rating" ).val(ui.values[ 0 ]);
      }
    });
    jQuery( "#star_rating" ).val(jQuery( "#slider-range" ).slider( "values", 0 ) );
  } );
  </script>
  <style>
  #custom-handle {
    width: 3em;
    height: 1.6em;
    top: 50%;
    margin-top: -.8em;
    text-align: center;
    line-height: 1.6em;
  }
  </style>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script>
  jQuery(document).ready(function() {
	 
	  
	  var handle = jQuery( "#custom-handle" );
	    jQuery( "#slider" ).slider({
      create: function() {
        handle.text(jQuery("#price").val());
       
      },
      slide: function( event, ui ) {
        handle.text(jQuery("#price").val());
		
      }
    });
     
	  
	});
  jQuery( function() {
    var handle = jQuery( "#custom-handle" );
    jQuery( "#slider" ).slider({
      create: function() {
        handle.text( jQuery( this ).slider( "value" ) );
        jQuery("#price").val( jQuery( this ).slider( "value" ) );
      },
      slide: function( event, ui ) {
        handle.text( ui.value );
		jQuery("#price").val( ui.value );
      }
    });
  } );
  
  
  jQuery('form#post').find('.categorychecklist input').each(function() {
	var new_input = jQuery('<input type="radio" />'),
		attrLen = this.attributes.length;
				
	for (i = 0; i < attrLen; i++) {
		if (this.attributes[i].name != 'type') {
			new_input.attr(this.attributes[i].name.toLowerCase(), this.attributes[i].value);
		}
	}
			
	jQuery(this).replaceWith(new_input);
});


  </script>
<?php
}
function sh_books_save_postdata($post_id){
if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

   if ( !wp_verify_nonce( $_POST['sh_book_noncename'],
    plugin_basename( __FILE__ ) ) )
      return;

   if ( !current_user_can( 'edit_post', $post_id ) )
        return;

  	if( isset( $_POST['price'] ) ){
		update_post_meta( $post_id,'price', 
        esc_attr( $_POST['price'] ) );
	}
  	if( isset( $_POST['description'] ) ){
		update_post_meta( $post_id,'description', 
        esc_attr( $_POST['description'] ) );
	}
	if( isset( $_POST['star_rating'] ) ){
		update_post_meta( $post_id,'star_rating', 
        esc_attr( $_POST['star_rating'] ) );
	}
	
	
}

//Enable Thumbnail
if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size( 150, 150 );
		add_image_size( 'book-thumb', 84, 107, true );
}


function sh_display($atts)
{
	extract( shortcode_atts( array('category' => ''), $atts ) );

	$args = array('post_type'=>'sh_books');
	
	if(isset($_GET['author'])&& $_GET['author'] !='')
	{
		$args['sh_book_category_author'] = trim($_GET['author']);
		
	}
	
	if(isset($_GET['publisher'])&& $_GET['publisher'] !='')
	{
		$args['sh_book_category_publisher'] = trim($_GET['publisher']);
		
	}

	$posts = new WP_Query( $args );
	$html='<div class="sh_holder">
	<div class="shelf">
	<div class="innerDiv" id="sh_book_1">';
  
		$html.='</div>
		</div>
  <table class="sh_book_tbl" cellspacing="0" cellpadding="0">';
    // The Loop 
$author_arr =  get_terms( array(
    'taxonomy' => 'sh_book_category_author',
    'hide_empty' => false,
) );
// $htm .= '<select id="author">';
// foreach($author_arr as $key=>$value)
// {
	
	
// }
// $htm .= '</select>';
$publisher_arr =  get_terms( array(
    'taxonomy' => 'sh_book_category_publisher',
    'hide_empty' => false,
) );
	
   if ( $posts->have_posts() ) : while ( $posts->have_posts() ) : $posts->the_post();

    $price =  get_post_meta( get_the_ID(), 'price', true );
    $description =  get_post_meta( get_the_ID(), 'description', true );
    $star_rating =  get_post_meta( get_the_ID(), 'star_rating', true );
	
	$author = get_the_terms( get_the_ID(),'sh_book_category_author',true);
	$publisher = get_the_terms( get_the_ID(),'sh_book_category_publisher',true);
	
  
    $html.='
    <tr>
 
  <td>'; 

    if($description && $price)
    { 
    
	$html.='<span >Book Title =>'.get_the_title( get_the_ID()).'</span><br />';
	$html.='<span >Book Author =>'.$author[0]->name.'</span><br />';
	$html.='<span >Book Publiser =>'.$publisher[0]->name.'</span><br />';
    $html.='<span >Book Description =>'.$description.'</span><br />';
    $html.='<span >Book Price =>'.$price.'</span><br />';
    $html.='<span >Star Rating =>'.$star_rating.'</span><br />';
    }elseif($price){
    $html.=$price;
    }else{
         $html.='';
    }
     $html.='</td>
    </tr>';
  endwhile;  endif;
    $html.='</table>';
 $html.='</div>
 <div>';
 $html .='<button value="save" class="search">Search Books</button>';

 
 $html.='
 </div><script>
 jQuery(".search").click(function(){
	
	alert("Work In Progress! Ajax searching is remaining :) ");
	return false;
});
 </script>';
 return $html;
}

	 add_shortcode( 'sh_books', 'sh_display' );
	 
	 function sh_add_styles()
{
	wp_register_style( 'sh_add_styles',
	 plugins_url('sh_books/style.css', __FILE__) );
    wp_enqueue_style( 'sh_add_styles' );
}

add_action( 'wp_enqueue_scripts', 'sh_add_styles' );